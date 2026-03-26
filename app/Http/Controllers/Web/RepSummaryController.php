<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepSummaryController extends Controller
{
    public function index()
    {
        $rep    = Auth::user();
        $county = $rep->county;

        if (! $county) {
            return view('rep.summary', [
                'county'         => null,
                'counts'         => null,
                'activationRate' => 0,
                'byWard'         => collect(),
            ]);
        }

        $counts = DB::table('facilities')
            ->where('county', $county)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN facility_status = 'ACTIVE'
                    THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN facility_status IN ('APPLIED','PPB_VERIFIED','ACCOUNT_LINKED')
                    THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN facility_status = 'CHURNED'
                    THEN 1 ELSE 0 END) as churned,
                SUM(CASE WHEN network_membership = 'NETWORK'
                    THEN 1 ELSE 0 END) as network_count,
                SUM(CASE WHEN network_membership = 'OFF_NETWORK'
                    THEN 1 ELSE 0 END) as off_network_count
            ")
            ->first();

        $activationRate = ($counts && $counts->total > 0)
            ? round(($counts->active / $counts->total) * 100)
            : 0;

        $byWard = DB::table('facilities')
            ->where('county', $county)
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw("
                ward,
                COUNT(*) as total,
                SUM(CASE WHEN facility_status = 'ACTIVE' THEN 1 ELSE 0 END) as active_count
            ")
            ->groupBy('ward')
            ->orderBy('total', 'desc')
            ->get();

        return view('rep.summary', compact('counts', 'activationRate', 'byWard', 'county'));
    }
}
