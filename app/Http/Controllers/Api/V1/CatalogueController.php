<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogueController extends Controller
{
    // GET /api/v1/catalogue
    public function index(Request $request): JsonResponse
    {
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
                'p.id as product_id',
                'p.ulid as product_ulid',
                'p.sku_code',
                'p.generic_name',
                'p.brand_name',
                'p.therapeutic_category',
                'p.unit_size',
                'wpl.id as price_list_id',
                'wpl.unit_price',
                'wpl.stock_status',
                'wpl.stock_quantity',
                'wpl.wholesale_facility_id',
                'wf.facility_name as wholesale_facility_name',
            ]);

        if ($request->filled('category')) {
            $query->where('p.therapeutic_category', $request->category);
        }

        if ($request->filled('wholesale_facility')) {
            $query->where('wpl.wholesale_facility_id', $request->wholesale_facility);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('p.generic_name', 'like', $search)
                  ->orWhere('p.brand_name', 'like', $search)
                  ->orWhere('p.sku_code', 'like', $search);
            });
        }

        $currency = CurrencyConfig::get();
        $products = $query->paginate(30);

        return response()->json([
            'products' => $products,
            'currency' => [
                'symbol'         => $currency['symbol'],
                'decimal_places' => $currency['decimal_places'],
            ],
        ]);
    }

    // GET /api/v1/catalogue/sync
    public function sync(Request $request): JsonResponse
    {
        // Returns full catalogue for offline cache
        // Used by Android app on connectivity restoration
        $products = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->join('facilities as wf', 'wpl.wholesale_facility_id', '=', 'wf.id')
            ->where('wpl.is_active', true)
            ->where('p.is_active', true)
            ->where('wpl.effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('wpl.expires_at')
                  ->orWhere('wpl.expires_at', '>=', now()->toDateString());
            })
            ->select([
                'p.id as product_id',
                'p.sku_code',
                'p.generic_name',
                'p.brand_name',
                'p.therapeutic_category',
                'p.unit_size',
                'wpl.id as price_list_id',
                'wpl.unit_price',
                'wpl.stock_status',
                'wpl.wholesale_facility_id',
            ])
            ->get();

        return response()->json([
            'products'   => $products,
            'synced_at'  => now('UTC')->toISOString(),
            'total'      => $products->count(),
        ]);
    }

    // GET /api/v1/catalogue/categories
    public function categories(): JsonResponse
    {
        $categories = DB::table('products')
            ->where('is_active', true)
            ->distinct()
            ->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');

        return response()->json(['categories' => $categories]);
    }
}
