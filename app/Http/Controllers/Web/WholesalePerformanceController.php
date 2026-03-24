<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WholesalePerformanceController extends Controller
{
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $totalOrders = DB::table('orders as o')
            ->whereExists(function ($q) use ($facilityId) {
                $q->select(DB::raw(1))->from('order_lines')
                  ->whereColumn('order_lines.order_id', 'o.id')
                  ->where('order_lines.wholesale_facility_id', $facilityId);
            })->whereNull('o.deleted_at')->count();

        $deliveredOrders = DB::table('orders as o')
            ->whereExists(function ($q) use ($facilityId) {
                $q->select(DB::raw(1))->from('order_lines')
                  ->whereColumn('order_lines.order_id', 'o.id')
                  ->where('order_lines.wholesale_facility_id', $facilityId);
            })->where('o.status', 'DELIVERED')->whereNull('o.deleted_at')->count();

        $fulfilmentRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;

        $totalRevenue = DB::table('order_lines')
            ->where('wholesale_facility_id', $facilityId)
            ->sum('line_total');

        $activePriceLists = DB::table('wholesale_price_lists')
            ->where('wholesale_facility_id', $facilityId)
            ->where('is_active', true)->count();

        return view('wholesale.performance', compact(
            'currency', 'totalOrders', 'deliveredOrders',
            'fulfilmentRate', 'totalRevenue', 'activePriceLists'
        ));
    }
}
