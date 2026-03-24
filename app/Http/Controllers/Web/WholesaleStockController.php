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

        $stockItems = DB::table('facility_stock_status as fss')
            ->join('products as p', 'fss.product_id', '=', 'p.id')
            ->where('fss.wholesale_facility_id', $facilityId)
            ->select(['fss.*', 'p.generic_name', 'p.sku_code', 'p.therapeutic_category'])
            ->orderBy('p.generic_name')
            ->paginate(30);

        $summary = [
            'in_stock'     => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'IN_STOCK')->count(),
            'low_stock'    => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'LOW_STOCK')->count(),
            'out_of_stock' => DB::table('facility_stock_status')->where('wholesale_facility_id', $facilityId)->where('stock_status', 'OUT_OF_STOCK')->count(),
        ];

        return view('wholesale.stock', compact('stockItems', 'summary'));
    }
}
