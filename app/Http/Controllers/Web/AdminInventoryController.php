<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminInventoryController extends Controller
{
    public function index()
    {
        $inventory = DB::table('facility_stock_status as fss')
            ->join('products as p', 'fss.product_id', '=', 'p.id')
            ->join('facilities as f', 'fss.wholesale_facility_id', '=', 'f.id')
            ->select(['fss.*', 'p.generic_name', 'p.sku_code',
                      'p.therapeutic_category', 'f.facility_name'])
            ->orderBy('f.facility_name')
            ->orderBy('p.generic_name')
            ->paginate(40);
        $summary = [
            'in_stock'     => DB::table('facility_stock_status')->where('stock_status','IN_STOCK')->count(),
            'low_stock'    => DB::table('facility_stock_status')->where('stock_status','LOW_STOCK')->count(),
            'out_of_stock' => DB::table('facility_stock_status')->where('stock_status','OUT_OF_STOCK')->count(),
        ];
        return view('admin.inventory', compact('inventory', 'summary'));
    }
}
