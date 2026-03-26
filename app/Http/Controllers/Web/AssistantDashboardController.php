<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * AssistantDashboardController
 *
 * assistant_admin, operational dashboard
 * Controller is a stub — business logic to be wired by Datanav.
 */
class AssistantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['assistant_admin', 'admin', 'super_admin', 'technical_admin'])) {
            return redirect('/dashboard');
        }

        $pendingRegistrations = DB::table('facilities')
            ->whereIn('facility_status', ['APPLIED', 'PPB_VERIFIED', 'ACCOUNT_LINKED'])
            ->whereNull('deleted_at')
            ->count();

        $activePlacers = DB::table('facility_authorised_placers')
            ->where('is_active', true)->count();

        $totalPlacers = DB::table('facility_authorised_placers')->count();

        $openDisputes = DB::table('delivery_disputes')
            ->where('status', 'OPEN')->count();

        $activeAlerts = 0;
        if (Schema::hasTable('business_metric_alerts')) {
            $activeAlerts = DB::table('business_metric_alerts')
                ->whereNull('acknowledged_at')
                ->whereIn('severity', ['CRITICAL', 'WARNING'])
                ->count();
        }

        $recentFacilities = DB::table('facilities')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->select(['ulid', 'facility_name', 'county', 'facility_status', 'created_at'])
            ->get();

        return view('assistant.dashboard', compact(
            'pendingRegistrations', 'activePlacers', 'totalPlacers',
            'openDisputes', 'activeAlerts', 'recentFacilities'
        ));
    }
}
