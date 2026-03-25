<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminCustomersController extends Controller
{
    public function index()
    {
        $customers = collect();
        try {
            $customers = DB::table('patient_orders')
                ->select(['patient_phone',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total_spend'),
                    DB::raw('MAX(created_at) as last_order_date')])
                ->groupBy('patient_phone')
                ->orderByDesc('total_spend')
                ->paginate(30);
        } catch (\Throwable) {
            // patient_orders may not have data yet
        }
        return view('admin.customers', compact('customers'));
    }
}
