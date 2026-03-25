<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailOrdersController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $orders = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('retail.orders', compact('orders', 'currency'));
    }

    public function show(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->first();

        if (! $order) abort(404);

        $lines = DB::table('order_lines as ol')
            ->join('products as p', 'ol.product_id', '=', 'p.id')
            ->join('facilities as wf', 'ol.wholesale_facility_id', '=', 'wf.id')
            ->where('ol.order_id', $order->id)
            ->select(['ol.*', 'p.generic_name', 'p.sku_code', 'wf.facility_name as supplier_name'])
            ->get();

        return view('retail.order-show', compact('order', 'lines', 'currency'));
    }
}
