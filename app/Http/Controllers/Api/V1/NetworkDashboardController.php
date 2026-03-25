<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ReportExportJob;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NetworkDashboardController extends Controller
{
    private function gateAdmin(Request $request): ?JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return null;
    }

    // GET /api/v1/network/dashboard/summary
    public function summary(Request $request): JsonResponse
    {
        if ($gate = $this->gateAdmin($request)) return $gate;

        $currency = CurrencyConfig::get();

        $today = Carbon::now('UTC')->toDateString();

        $summaries = DB::table('network_daily_summaries')
            ->where('summary_date', $today)
            ->whereNull('county')
            ->get()
            ->keyBy('network_membership');

        $allStats = $summaries->get('ALL');

        return response()->json([
            'date'               => $today,
            'total_orders'       => $allStats?->total_orders ?? 0,
            'total_gmv'          => $allStats?->total_gmv ?? 0,
            'active_facilities'  => $allStats?->active_facilities ?? 0,
            'new_facilities'     => $allStats?->new_facilities ?? 0,
            'network_breakdown'  => [
                'NETWORK'     => $summaries->get('NETWORK'),
                'OFF_NETWORK' => $summaries->get('OFF_NETWORK'),
            ],
            'currency'           => $currency['symbol'],
            'computed_at'        => $allStats?->computed_at,
        ]);
    }

    // GET /api/v1/network/gmv
    public function gmv(Request $request): JsonResponse
    {
        if ($gate = $this->gateAdmin($request)) return $gate;

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo   = $request->get('date_to', Carbon::now()->toDateString());
        $county   = $request->get('county');
        $membership = $request->get('membership');

        $query = DB::table('network_daily_summaries')
            ->whereBetween('summary_date', [$dateFrom, $dateTo])
            ->whereNull('facility_type');

        if ($county) $query->where('county', $county);
        if ($membership) $query->where('network_membership', $membership);
        else $query->where('network_membership', 'ALL');

        $data = $query->orderBy('summary_date')->get();

        $currency = CurrencyConfig::get();

        return response()->json([
            'data'      => $data,
            'currency'  => $currency['symbol'],
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]);
    }

    // GET /api/v1/network/membership-comparison
    public function membershipComparison(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $days = $request->get('days', 30);
        $dateFrom = Carbon::now()->subDays($days)->toDateString();

        $network = DB::table('network_daily_summaries')
            ->where('network_membership', 'NETWORK')
            ->where('summary_date', '>=', $dateFrom)
            ->whereNull('county')
            ->whereNull('facility_type')
            ->selectRaw('
                SUM(total_orders) as total_orders,
                SUM(total_gmv) as total_gmv,
                AVG(avg_order_value) as avg_order_value,
                AVG(active_facilities) as avg_active_facilities
            ')
            ->first();

        $offNetwork = DB::table('network_daily_summaries')
            ->where('network_membership', 'OFF_NETWORK')
            ->where('summary_date', '>=', $dateFrom)
            ->whereNull('county')
            ->whereNull('facility_type')
            ->selectRaw('
                SUM(total_orders) as total_orders,
                SUM(total_gmv) as total_gmv,
                AVG(avg_order_value) as avg_order_value,
                AVG(active_facilities) as avg_active_facilities
            ')
            ->first();

        $currency = CurrencyConfig::get();

        return response()->json([
            'period_days' => $days,
            'network'     => $network,
            'off_network' => $offNetwork,
            'currency'    => $currency['symbol'],
        ]);
    }

    // GET /api/v1/network/groups/{groupUlid}/performance
    public function groupPerformance(Request $request, string $groupUlid): JsonResponse
    {
        if ($gate = $this->gateAdmin($request)) return $gate;

        $group = DB::table('pharmacy_groups')
            ->where('ulid', $groupUlid)
            ->first();

        if (! $group) {
            return response()->json(['message' => 'Group not found.'], 404);
        }

        $facilityIds = DB::table('pharmacy_group_members')
            ->where('group_id', $group->id)
            ->pluck('facility_id');

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $orderStats = DB::table('orders')
            ->whereIn('retail_facility_id', $facilityIds)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_gmv,
                COALESCE(SUM(credit_amount), 0) as total_credit,
                COALESCE(SUM(cash_amount), 0) as total_cash
            ')
            ->first();

        $currency = CurrencyConfig::get();

        return response()->json([
            'group'        => $group,
            'member_count' => $facilityIds->count(),
            'orders'       => $orderStats,
            'currency'     => $currency['symbol'],
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
        ]);
    }

    // POST /api/v1/network/reports/export
    public function exportReport(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'export_type'        => 'required|in:network_summary,facility_performance,order_history,credit_overview',
            'metric_definitions' => 'required|array',
            'parameters'         => 'nullable|array',
        ]);

        $now = Carbon::now('UTC');

        $exportId = DB::table('reporting_exports')->insertGetId([
            'exported_by'        => $request->user()->id,
            'export_type'        => $validated['export_type'],
            'metric_definitions' => json_encode($validated['metric_definitions']),
            'parameters'         => json_encode($validated['parameters'] ?? []),
            'status'             => 'QUEUED',
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        ReportExportJob::dispatch($exportId)->onQueue('reports');

        return response()->json([
            'message'   => 'Report export queued.',
            'export_id' => $exportId,
        ], 201);
    }

    // GET /api/v1/network/reports/{id}
    public function reportStatus(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $export = DB::table('reporting_exports')
            ->where('id', $id)
            ->first();

        if (! $export) {
            return response()->json(['message' => 'Export not found.'], 404);
        }

        return response()->json(['export' => $export]);
    }
}
