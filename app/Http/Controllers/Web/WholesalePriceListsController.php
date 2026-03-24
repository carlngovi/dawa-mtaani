<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WholesalePriceListsController extends Controller
{
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $priceLists = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->where('wpl.wholesale_facility_id', $facilityId)
            ->select(['wpl.*', 'p.generic_name', 'p.brand_name', 'p.sku_code', 'p.therapeutic_category'])
            ->orderBy('p.generic_name')
            ->paginate(30);

        $expiredCount = DB::table('wholesale_price_lists')
            ->where('wholesale_facility_id', $facilityId)
            ->where('is_active', false)
            ->where('expires_at', '>=', now()->subDays(30)->toDateString())
            ->count();

        return view('wholesale.price-lists', compact('priceLists', 'currency', 'expiredCount'));
    }
}
