<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WholesalePerformanceController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->user()->facility_id;
        $currency = CurrencyConfig::get();

        $totalOrders = DB::table('orders as o')->whereExists(function ($q) use ($facilityId) {
            $q->select(DB::raw(1))->from('order_lines')->whereColumn('order_lines.order_id', 'o.id')->where('order_lines.wholesale_facility_id', $facilityId);
        })->whereNull('o.deleted_at')->count();

        $deliveredOrders = DB::table('orders as o')->whereExists(function ($q) use ($facilityId) {
            $q->select(DB::raw(1))->from('order_lines')->whereColumn('order_lines.order_id', 'o.id')->where('order_lines.wholesale_facility_id', $facilityId);
        })->where('o.status', 'DELIVERED')->whereNull('o.deleted_at')->count();

        $totalRevenue = DB::table('order_lines')->where('wholesale_facility_id', $facilityId)->sum('line_total');
        $fulfilmentRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;
        $activePriceLists = DB::table('wholesale_price_lists')->where('wholesale_facility_id', $facilityId)->where('is_active', true)->count();

        $topBuyers = DB::table('order_lines as ol')
            ->join('orders as o', 'ol.order_id', '=', 'o.id')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('ol.wholesale_facility_id', $facilityId)->whereNull('o.deleted_at')
            ->select(['f.facility_name', 'f.county', DB::raw('COUNT(DISTINCT o.id) as order_count'), DB::raw('SUM(ol.line_total) as total_spend')])
            ->groupBy('f.id', 'f.facility_name', 'f.county')->orderByDesc('total_spend')->limit(10)->get();

        $topProducts = DB::table('order_lines as ol')
            ->join('orders as o', 'ol.order_id', '=', 'o.id')
            ->join('products as p', 'ol.product_id', '=', 'p.id')
            ->where('ol.wholesale_facility_id', $facilityId)->whereNull('o.deleted_at')
            ->select(['p.generic_name', 'p.sku_code', DB::raw('SUM(ol.quantity) as total_units'), DB::raw('SUM(ol.line_total) as total_revenue')])
            ->groupBy('p.id', 'p.generic_name', 'p.sku_code')->orderByDesc('total_revenue')->limit(10)->get();

        return view('wholesale.performance', compact('currency', 'totalOrders', 'deliveredOrders', 'totalRevenue', 'fulfilmentRate', 'activePriceLists', 'topBuyers', 'topProducts'));
    }
}
