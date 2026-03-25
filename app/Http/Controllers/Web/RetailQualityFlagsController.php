<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailQualityFlagsController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->user()->facility_id;

        $myFlags = DB::table('quality_flags as q')
            ->leftJoin('products as p', 'q.product_id', '=', 'p.id')
            ->where('q.facility_id', $facilityId)
            ->select(['q.*', 'p.generic_name', 'p.sku_code'])
            ->orderByDesc('q.created_at')
            ->paginate(20);

        $products = DB::table('products')->where('is_active', true)->orderBy('generic_name')->get(['id', 'generic_name', 'sku_code']);

        return view('retail.quality-flags', compact('myFlags', 'products', 'facilityId'));
    }
}
