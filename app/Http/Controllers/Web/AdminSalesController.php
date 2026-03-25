<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\DB;
class AdminSalesController extends Controller
{
    public function index()
    {
        $currency = CurrencyConfig::get();
        $salesByFacility = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereNull('o.deleted_at')
            ->select(['f.facility_name', 'f.county', 'f.network_membership',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(o.total_amount) as total_gmv'),
                DB::raw('MAX(o.created_at) as last_order_date')])
            ->groupBy('f.id', 'f.facility_name', 'f.county', 'f.network_membership')
            ->orderByDesc('total_gmv')
            ->paginate(30);
        return view('admin.sales', compact('salesByFacility', 'currency'));
    }
}
