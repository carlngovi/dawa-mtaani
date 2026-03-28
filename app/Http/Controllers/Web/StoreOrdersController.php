<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreOrdersController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $orders = DB::table('customer_orders as po')
            ->leftJoin('facilities as f', 'po.facility_id', '=', 'f.id')
            ->where('po.user_id', $user->id)
            ->when($request->filled('status'), fn($q) => $q->where('po.status', $request->status))
            ->select([
                'po.id', 'po.ulid', 'po.status', 'po.total_amount',
                'po.collection_window_start', 'po.collection_window_end',
                'po.paid_at', 'po.collected_at', 'po.created_at',
                'f.facility_name', 'f.county',
            ])
            ->orderBy('po.created_at', 'desc')
            ->paginate(15)->withQueryString();

        $stats = [
            'total'     => DB::table('customer_orders')->where('user_id', $user->id)->count(),
            'pending'   => DB::table('customer_orders')->where('user_id', $user->id)->whereIn('status', ['PAYMENT_PENDING', 'CONFIRMED', 'READY'])->count(),
            'collected' => DB::table('customer_orders')->where('user_id', $user->id)->where('status', 'COLLECTED')->count(),
        ];

        return view('store.orders', compact('orders', 'stats', 'currency'));
    }

    public function show(string $ulid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $order = DB::table('customer_orders as po')
            ->leftJoin('facilities as f', 'po.facility_id', '=', 'f.id')
            ->where('po.ulid', $ulid)
            ->where('po.user_id', $user->id)
            ->select([
                'po.*',
                'f.facility_name', 'f.county', 'f.physical_address', 'f.ulid as facility_ulid',
            ])
            ->first();

        abort_if(! $order, 404);

        $lines = DB::table('customer_order_lines as pol')
            ->join('products as p', 'p.id', '=', 'pol.product_id')
            ->where('pol.customer_order_id', $order->id)
            ->select([
                'p.generic_name', 'p.brand_name', 'p.unit_size',
                'pol.quantity', 'pol.unit_price', 'pol.line_total',
            ])
            ->get();

        $isPending = request('pending') === 'true';

        return view('store.order-detail', compact('order', 'lines', 'currency', 'isPending'));
    }
}
