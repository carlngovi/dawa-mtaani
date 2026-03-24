<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrdersController extends Controller
{
    public function index(Request $request)
    {
        $currency = CurrencyConfig::get();

        $query = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereNull('o.deleted_at')
            ->select(['o.*', 'f.facility_name', 'f.county', 'f.network_membership'])
            ->orderBy('o.created_at', 'desc');

        if ($request->filled('status'))     $query->where('o.status', $request->status);
        if ($request->filled('membership')) $query->where('f.network_membership', $request->membership);
        if ($request->filled('channel'))    $query->where('o.source_channel', $request->channel);
        if ($request->filled('date_from'))  $query->where('o.created_at', '>=', $request->date_from);

        $orders = $query->paginate(30)->withQueryString();

        $stats = [
            'total_today'   => DB::table('orders')->whereDate('created_at', today())->whereNull('deleted_at')->count(),
            'pending'       => DB::table('orders')->where('status','PENDING')->whereNull('deleted_at')->count(),
            'disputed'      => DB::table('orders')->where('status','DISPUTED')->whereNull('deleted_at')->count(),
        ];

        return view('admin.orders', compact('orders', 'currency', 'stats'));
    }
}
