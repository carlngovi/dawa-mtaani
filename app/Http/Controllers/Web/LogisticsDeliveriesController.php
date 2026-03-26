<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogisticsDeliveriesController extends Controller
{
    public function index(Request $request)
    {
        $currency = CurrencyConfig::get();

        $deliveries = DB::table('delivery_confirmations as dc')
            ->join('orders as o', 'dc.order_id', '=', 'o.id')
            ->join('facilities as retail', 'o.retail_facility_id', '=', 'retail.id')
            ->whereNull('o.deleted_at')
            ->when($request->filled('county'), fn($q) => $q->where('retail.county', $request->county))
            ->when($request->filled('status'), fn($q) => $q->where('o.status', $request->status))
            ->select([
                'dc.id', 'dc.confirmation_method', 'dc.confirmed_at',
                'o.ulid as order_ulid', 'o.status', 'o.total_amount',
                'retail.facility_name', 'retail.county', 'retail.ward',
            ])
            ->orderByRaw("CASE WHEN o.status='DISPATCHED' THEN 0 WHEN o.status='CONFIRMED' THEN 1 ELSE 2 END")
            ->orderBy('o.updated_at', 'asc')
            ->paginate(25)->withQueryString();

        $stats = [
            'dispatched'      => DB::table('orders')->where('status', 'DISPATCHED')->whereNull('deleted_at')->count(),
            'delivered_today' => DB::table('delivery_confirmations')->whereDate('confirmed_at', today())->count(),
            'open_disputes'   => DB::table('delivery_disputes')->where('status', 'OPEN')->count(),
        ];

        $counties = DB::table('facilities')
            ->whereNotNull('county')->whereNull('deleted_at')
            ->distinct()->orderBy('county')->pluck('county');

        return view('logistics.deliveries', compact('deliveries', 'stats', 'counties', 'currency'));
    }
}
