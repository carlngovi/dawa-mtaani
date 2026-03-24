<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')->where('id', $facilityId)->first();

        if (! $facility) {
            return view('retail.dashboard', [
                'facility'        => null,
                'currency'        => $currency,
                'recentOrders'    => collect(),
                'pendingDisputes' => 0,
                'monthGmv'        => 0,
                'totalOrders'     => 0,
                'lowStockCount'   => 0,
            ]);
        }

        $recentOrders = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingDisputes = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->join('orders as o', 'dc.order_id', '=', 'o.id')
            ->where('o.retail_facility_id', $facilityId)
            ->where('dd.status', '!=', 'RESOLVED')
            ->count();

        $monthGmv = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->whereNull('deleted_at')
            ->sum('total_amount');

        $totalOrders = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->count();

        // Use facility_stock_status table — no separate stock_levels table exists
        $lowStockCount = DB::table('facility_stock_status')
            ->where('wholesale_facility_id', $facilityId)
            ->where('stock_status', 'LOW_STOCK')
            ->count();

        return view('retail.dashboard', compact(
            'facility', 'currency', 'recentOrders',
            'pendingDisputes', 'monthGmv', 'totalOrders', 'lowStockCount'
        ));
    }
}
