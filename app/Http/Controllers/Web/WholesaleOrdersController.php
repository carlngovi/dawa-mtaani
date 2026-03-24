<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WholesaleOrdersController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        $currency = CurrencyConfig::get();

        $query = DB::table('orders as o')
            ->join('facilities as rf', 'o.retail_facility_id', '=', 'rf.id')
            ->whereNull('o.deleted_at')
            ->whereExists(function ($sub) use ($facilityId) {
                $sub->select(DB::raw(1))
                    ->from('order_lines')
                    ->whereColumn('order_lines.order_id', 'o.id')
                    ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->select(['o.*', 'rf.facility_name as retail_name', 'rf.county', 'rf.ward'])
            ->orderBy('o.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('o.status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'pending'    => DB::table('orders as o')
                ->whereExists(function ($sub) use ($facilityId) {
                    $sub->select(DB::raw(1))->from('order_lines')
                        ->whereColumn('order_lines.order_id', 'o.id')
                        ->where('order_lines.wholesale_facility_id', $facilityId);
                })->where('o.status', 'PENDING')->count(),
            'confirmed'  => DB::table('orders as o')
                ->whereExists(function ($sub) use ($facilityId) {
                    $sub->select(DB::raw(1))->from('order_lines')
                        ->whereColumn('order_lines.order_id', 'o.id')
                        ->where('order_lines.wholesale_facility_id', $facilityId);
                })->whereIn('o.status', ['CONFIRMED','PICKING','PACKED'])->count(),
            'dispatched' => DB::table('orders as o')
                ->whereExists(function ($sub) use ($facilityId) {
                    $sub->select(DB::raw(1))->from('order_lines')
                        ->whereColumn('order_lines.order_id', 'o.id')
                        ->where('order_lines.wholesale_facility_id', $facilityId);
                })->where('o.status', 'DISPATCHED')->count(),
        ];

        return view('wholesale.orders', compact('orders', 'currency', 'stats'));
    }
}
