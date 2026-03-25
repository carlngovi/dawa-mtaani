<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailFavouritesController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->user()->facility_id;
        $currency = CurrencyConfig::get();

        $favourites = DB::table('facility_favourite_products as f')
            ->join('products as p', 'f.product_id', '=', 'p.id')
            ->leftJoin('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.product_id', '=', 'p.id')->where('wpl.is_active', true)->where('wpl.stock_status', '!=', 'OUT_OF_STOCK');
            })
            ->where('f.facility_id', $facilityId)
            ->select(['p.id as product_id', 'p.generic_name', 'p.brand_name', 'p.sku_code', 'p.therapeutic_category', 'p.unit_size', 'f.created_at as favourited_at',
                DB::raw('MIN(wpl.unit_price) as lowest_price'), DB::raw('COUNT(DISTINCT wpl.wholesale_facility_id) as supplier_count')])
            ->groupBy('p.id', 'p.generic_name', 'p.brand_name', 'p.sku_code', 'p.therapeutic_category', 'p.unit_size', 'f.created_at')
            ->orderBy('p.generic_name')
            ->paginate(30);

        return view('retail.favourites', compact('favourites', 'currency', 'facilityId'));
    }
}
