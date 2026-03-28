<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WholesaleStockController extends Controller
{
    public function index()
    {
        $facilityId = Auth::user()->facility_id;
        $search = request('search');

        $stockItems = DB::table('facility_stock_status as fss')
            ->join('products as p', 'fss.product_id', '=', 'p.id')
            ->where('fss.wholesale_facility_id', $facilityId)
            ->when($search, function ($q) use ($search) {
                $q->where(function($inner) use ($search) {
                    $inner->where('p.generic_name', 'like', '%' . $search . '%')
                          ->orWhere('p.sku_code', 'like', '%' . $search . '%');
                });
            })
            ->select(['fss.*', 'p.generic_name', 'p.sku_code', 'p.therapeutic_category'])
            ->orderBy('p.generic_name')
            ->paginate(30);

        $summary = [
            'in_stock'     => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'IN_STOCK')->count(),
            'low_stock'    => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'LOW_STOCK')->count(),
            'out_of_stock' => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'OUT_OF_STOCK')->count(),
        ];

        return view('wholesale.stock', compact('stockItems', 'summary', 'search'));
    }

    public function show($productId)
    {
        $facilityId = Auth::user()->facility_id;

        $item = DB::table('facility_stock_status as fss')
            ->join('products as p', 'fss.product_id', '=', 'p.id')
            ->where('fss.wholesale_facility_id', $facilityId)
            ->where('fss.product_id', $productId)
            ->select(['fss.*', 'p.generic_name', 'p.sku_code', 'p.therapeutic_category', 'p.dosage_form', 'p.strength', 'p.manufacturer'])
            ->first();

        if (!$item) {
            abort(404);
        }

        return view('wholesale.stock-show', compact('item'));
    }
}
