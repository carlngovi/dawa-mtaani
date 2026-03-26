<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreOrdersController extends Controller
{
    private function phoneVariants(?string $phone): array
    {
        if (! $phone) return [''];
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '254')) {
            return [$digits, '0' . substr($digits, 3), '+' . $digits];
        }
        if (str_starts_with($digits, '0')) {
            $intl = '254' . substr($digits, 1);
            return [$digits, $intl, '+' . $intl];
        }
        return [$digits];
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();
        $phones   = $this->phoneVariants($user->phone);

        $orders = DB::table('patient_orders as po')
            ->leftJoin('facilities as f', 'po.facility_id', '=', 'f.id')
            ->whereIn('po.patient_phone', $phones)
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
            'total'     => DB::table('patient_orders')->whereIn('patient_phone', $phones)->count(),
            'pending'   => DB::table('patient_orders')->whereIn('patient_phone', $phones)->whereIn('status', ['PAYMENT_PENDING', 'CONFIRMED'])->count(),
            'collected' => DB::table('patient_orders')->whereIn('patient_phone', $phones)->where('status', 'COLLECTED')->count(),
        ];

        return view('store.orders', compact('orders', 'stats', 'currency'));
    }

    public function show(string $ulid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $phones = $this->phoneVariants($user->phone);

        $order = DB::table('patient_orders as po')
            ->leftJoin('facilities as f', 'po.facility_id', '=', 'f.id')
            ->where('po.ulid', $ulid)
            ->whereIn('po.patient_phone', $phones)
            ->select([
                'po.*',
                'f.facility_name', 'f.county', 'f.physical_address', 'f.ulid as facility_ulid',
            ])
            ->first();

        abort_if(! $order, 404);

        $lines = DB::table('patient_order_lines as pol')
            ->join('products as p', 'p.id', '=', 'pol.product_id')
            ->where('pol.patient_order_id', $order->id)
            ->select([
                'p.generic_name', 'p.brand_name', 'p.unit_size',
                'pol.quantity', 'pol.unit_price', 'pol.line_total',
            ])
            ->get();

        $isPending = request('pending') === 'true';

        return view('store.order-detail', compact('order', 'lines', 'currency', 'isPending'));
    }
}
