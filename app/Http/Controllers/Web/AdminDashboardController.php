<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAnyRole([
            'network_admin', 'admin', 'super_admin', 'technical_admin',
            'assistant_admin', 'admin_support', 'network_field_agent',
        ])) {
            return redirect('/dashboard');
        }

        $currency = CurrencyConfig::get();
        $today = Carbon::now('UTC')->toDateString();

        // Load from pre-computed summaries — never live aggregate
        $todaySummary = DB::table('network_daily_summaries')
            ->where('summary_date', $today)
            ->where('network_membership', 'ALL')
            ->whereNull('county')
            ->whereNull('facility_type')
            ->first();

        $networkSummary = DB::table('network_daily_summaries')
            ->where('summary_date', $today)
            ->where('network_membership', 'NETWORK')
            ->whereNull('county')
            ->whereNull('facility_type')
            ->first();

        $offNetworkSummary = DB::table('network_daily_summaries')
            ->where('summary_date', $today)
            ->where('network_membership', 'OFF_NETWORK')
            ->whereNull('county')
            ->whereNull('facility_type')
            ->first();

        // Active alerts
        $activeAlerts = DB::table('business_metric_alerts')
            ->whereNull('acknowledged_at')
            ->whereIn('severity', ['CRITICAL', 'WARNING'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent orders (last 24h)
        $recentOrders = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereNull('o.deleted_at')
            ->select(['o.ulid', 'o.status', 'o.total_amount', 'o.created_at', 'f.facility_name', 'f.county'])
            ->orderBy('o.created_at', 'desc')
            ->limit(10)
            ->get();

        // Open disputes
        $openDisputes = DB::table('delivery_disputes')
            ->where('status', 'OPEN')
            ->count();

        // Facilities with no GPS
        $gpsPendingCount = DB::table('facilities')
            ->whereNull('latitude')
            ->where('facility_status', 'ACTIVE')
            ->count();

        // PPB registry staleness
        $ppbStale = DB::table('system_settings')
            ->where('key', 'ppb_registry_stale_days')
            ->value('value') ?? 7;

        $lastPpbUpload = DB::table('ppb_registry_cache')
            ->max('last_uploaded_at');

        $ppbIsStale = ! $lastPpbUpload ||
            Carbon::parse($lastPpbUpload)->diffInDays(now()) > (int) $ppbStale;

        return view('admin.dashboard', compact(
            'currency',
            'todaySummary',
            'networkSummary',
            'offNetworkSummary',
            'activeAlerts',
            'recentOrders',
            'openDisputes',
            'gpsPendingCount',
            'ppbIsStale',
            'today'
        ));
    }
}
