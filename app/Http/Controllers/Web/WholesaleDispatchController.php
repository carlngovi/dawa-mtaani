<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * WholesaleDispatchController
 *
 * wholesale_facility, dispatch trigger and tracking
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleDispatchController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        $currency   = CurrencyConfig::get();

        $packedOrders = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('o.status', 'PACKED')
            ->whereNull('o.deleted_at')
            ->whereExists(function ($sub) use ($facilityId) {
                $sub->select(DB::raw(1))
                    ->from('order_lines')
                    ->whereColumn('order_lines.order_id', 'o.id')
                    ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->select(['o.id', 'o.ulid', 'o.total_amount', 'o.payment_type', 'o.updated_at',
                      'f.facility_name', 'f.county', 'f.ward'])
            ->orderBy('o.updated_at', 'asc')
            ->paginate(25)->withQueryString();

        $dispatchedToday = DB::table('orders')
            ->where('status', 'DISPATCHED')
            ->whereDate('updated_at', today())
            ->count();

        return view('wholesale.dispatch', compact('packedOrders', 'dispatchedToday', 'currency'));
    }
}
