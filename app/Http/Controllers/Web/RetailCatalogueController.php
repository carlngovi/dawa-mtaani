<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use App\Services\PricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailCatalogueController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        $currency = CurrencyConfig::get();

        $isOffNetwork = false;
        $favouriteIds = [];

        if ($facilityId) {
            $pricingEngine = app(PricingEngine::class);
            $isOffNetwork = $pricingEngine->isOffNetwork($facilityId);

            $favouriteIds = DB::table('facility_favourite_products')
                ->where('facility_id', $facilityId)
                ->pluck('product_id')
                ->toArray();
        }

        $query = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->join('facilities as wf', 'wpl.wholesale_facility_id', '=', 'wf.id')
            ->where('wpl.is_active', true)
            ->where('wpl.stock_status', '!=', 'OUT_OF_STOCK')
            ->where('p.is_active', true)
            ->where('wpl.effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('wpl.expires_at')
                  ->orWhere('wpl.expires_at', '>=', now()->toDateString());
            })
            ->select([
                'p.id as product_id', 'p.ulid', 'p.sku_code',
                'p.generic_name', 'p.brand_name', 'p.therapeutic_category',
                'p.unit_size', 'wpl.id as price_list_id', 'wpl.unit_price',
                'wpl.stock_status', 'wpl.wholesale_facility_id',
                'wf.facility_name as supplier_name',
            ]);

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('p.generic_name', 'like', $s)
                  ->orWhere('p.brand_name', 'like', $s)
                  ->orWhere('p.sku_code', 'like', $s);
            });
        }

        if ($request->filled('category')) {
            $query->where('p.therapeutic_category', $request->category);
        }

        $products = $query->paginate(24)->withQueryString();

        $categories = DB::table('products')
            ->where('is_active', true)
            ->distinct()
            ->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');

        return view('retail.catalogue', compact(
            'products', 'categories', 'currency',
            'favouriteIds', 'isOffNetwork', 'facilityId'
        ));
    }

    public function cart()
    {
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $currency = CurrencyConfig::get();

        $allProducts = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->where('wpl.is_active', true)
            ->where('p.is_active', true)
            ->where('wpl.stock_status', '!=', 'OUT_OF_STOCK')
            ->select([
                'p.id', 'p.generic_name', 'p.brand_name', 'p.sku_code',
                'p.unit_size', 'wpl.id as price_list_id',
                'wpl.unit_price', 'wpl.stock_status',
            ])
            ->orderBy('p.generic_name')
            ->get();

        return view('retail.cart', compact('allProducts', 'currency'));
    }
}
