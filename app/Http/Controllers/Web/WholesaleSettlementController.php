<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * WholesaleSettlementController
 *
 * wholesale_facility, NILA settlement view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleSettlementController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        $currency   = CurrencyConfig::get();

        $records      = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        $latestRecord = null;

        if (\Illuminate\Support\Facades\Schema::hasTable('settlement_records')) {
            $records = DB::table('settlement_records')
                ->where('facility_id', $facilityId)
                ->orderBy('created_at', 'desc')
                ->paginate(12)->withQueryString();

            $latestRecord = DB::table('settlement_records')
                ->where('facility_id', $facilityId)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return view('wholesale.settlement', compact('records', 'latestRecord', 'currency'));
    }
}
