<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringDashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $since = Carbon::now('UTC')->subHours(24);

        $recentSnapshots = DB::table('business_metric_snapshots')
            ->where('window_start', '>=', $since)
            ->orderBy('window_start', 'desc')
            ->limit(100)
            ->get()
            ->groupBy('metric_name');

        $activeAlerts = DB::table('business_metric_alerts')
            ->whereNull('acknowledged_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $sloRecords = DB::table('slo_compliance_records')
            ->orderBy('period_start', 'desc')
            ->limit(30)
            ->get()
            ->groupBy('sli_name');

        return response()->json([
            'metrics'      => $recentSnapshots,
            'active_alerts' => $activeAlerts,
            'slo_summary'  => $sloRecords,
            'generated_at' => Carbon::now('UTC')->toISOString(),
        ]);
    }

    public function acknowledgeAlert(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $updated = DB::table('business_metric_alerts')
            ->where('id', $id)
            ->whereNull('acknowledged_at')
            ->update([
                'acknowledged_at' => Carbon::now('UTC'),
                'acknowledged_by' => $request->user()->id,
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Alert not found or already acknowledged.'], 404);
        }

        return response()->json(['message' => 'Alert acknowledged.']);
    }

    public function sloCompliance(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $records = DB::table('slo_compliance_records')
            ->orderBy('period_start', 'desc')
            ->limit(120)
            ->get()
            ->groupBy('sli_name');

        return response()->json([
            'slo_compliance' => $records,
            'generated_at'   => Carbon::now('UTC')->toISOString(),
        ]);
    }
}
