<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailFavouritesController extends Controller
{
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $favourites = DB::table('facility_favourite_products as ffp')
            ->join('products as p', 'ffp.product_id', '=', 'p.id')
            ->leftJoin('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.product_id', '=', 'p.id')
                     ->where('wpl.is_active', true)
                     ->where('wpl.stock_status', '!=', 'OUT_OF_STOCK');
            })
            ->where('ffp.facility_id', $facilityId)
            ->where('p.is_active', true)
            ->select(['p.id as product_id', 'p.generic_name', 'p.sku_code',
                      'p.unit_size', 'p.therapeutic_category',
                      'wpl.unit_price', 'wpl.stock_status', 'wpl.id as price_list_id'])
            ->orderBy('p.generic_name')
            ->get();

        return view('retail.favourites', compact('favourites', 'currency'));
    }
}
