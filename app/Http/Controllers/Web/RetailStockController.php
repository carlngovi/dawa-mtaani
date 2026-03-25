<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailStockController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->user()->facility_id;
        $currency   = CurrencyConfig::get();

        $search   = $request->get('search');
        $category = $request->get('category');
        $status   = $request->get('status');

        $query = DB::table('facility_stock_status as fss')
            ->join('products as p', 'fss.product_id', '=', 'p.id')
            ->join('facilities as f', 'fss.wholesale_facility_id', '=', 'f.id')
            ->select([
                'fss.id', 'fss.stock_status', 'fss.stock_quantity', 'fss.updated_at',
                'p.id as product_id', 'p.generic_name', 'p.brand_name', 'p.sku_code',
                'p.therapeutic_category', 'p.unit_size',
                'f.id as supplier_id', 'f.facility_name as supplier_name', 'f.county as supplier_county',
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.generic_name', 'like', "%{$search}%")
                  ->orWhere('p.sku_code', 'like', "%{$search}%")
                  ->orWhere('p.brand_name', 'like', "%{$search}%");
            });
        }
        if ($category) $query->where('p.therapeutic_category', $category);
        if ($status) $query->where('fss.stock_status', $status);

        $stockItems = $query->orderBy('p.therapeutic_category')->orderBy('p.generic_name')->paginate(40);

        $summary = [
            'in_stock'     => DB::table('facility_stock_status')->where('stock_status', 'IN_STOCK')->count(),
            'low_stock'    => DB::table('facility_stock_status')->where('stock_status', 'LOW_STOCK')->count(),
            'out_of_stock' => DB::table('facility_stock_status')->where('stock_status', 'OUT_OF_STOCK')->count(),
        ];

        $categories = DB::table('products')->distinct()->orderBy('therapeutic_category')->pluck('therapeutic_category');

        $prices = DB::table('wholesale_price_lists as wpl')
            ->where('wpl.is_active', true)
            ->select(['wpl.product_id', 'wpl.wholesale_facility_id', 'wpl.unit_price'])
            ->get()
            ->groupBy('product_id');

        return view('retail.stock', compact('stockItems', 'summary', 'categories', 'currency', 'search', 'category', 'status', 'prices'));
    }
}
