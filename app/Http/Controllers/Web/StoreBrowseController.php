<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreBrowseController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $query = DB::table('products as p')
            ->leftJoin('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.product_id', '=', 'p.id')
                     ->where('wpl.is_active', true);
            })
            ->where('p.is_active', true)
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('p.generic_name', 'like', '%' . $request->search . '%')
                  ->orWhere('p.brand_name', 'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('category'), fn($q) => $q->where('p.therapeutic_category', $request->category))
            ->select([
                'p.id', 'p.generic_name', 'p.brand_name', 'p.unit_size',
                'p.therapeutic_category',
                DB::raw('COALESCE(wpl.unit_price, 0) as unit_price'),
                DB::raw("COALESCE(wpl.stock_status, 'IN_STOCK') as stock_status"),
            ])
            ->orderBy('p.generic_name');

        $products = $query->paginate(24)->withQueryString();

        $categories = DB::table('products')
            ->where('is_active', true)
            ->whereNotNull('therapeutic_category')
            ->distinct()
            ->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');

        $basketCount = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->join('patient_basket_lines', 'patient_baskets.id', '=', 'patient_basket_lines.basket_id')
            ->sum('patient_basket_lines.quantity');

        // Eligible pharmacies for linking from search results
        $eligibleFacilities = DB::table('online_store_eligible_facilities as osef')
            ->join('facilities as f', 'f.id', '=', 'osef.facility_id')
            ->where('osef.is_active', true)
            ->where('f.facility_status', 'ACTIVE')
            ->whereNull('f.deleted_at')
            ->select(['f.ulid', 'f.facility_name', 'f.county', 'f.network_membership', 'osef.branding_mode'])
            ->orderBy('f.facility_name')
            ->limit(50)
            ->get();

        return view('store.browse', compact('products', 'categories', 'currency', 'basketCount', 'eligibleFacilities'));
    }

    public function basket()
    {
        $currency = CurrencyConfig::get();
        return view('store.basket', compact('currency'));
    }

    public function patientCheckout(Request $request)
    {
        $request->validate([
            'phone'                 => 'required|string|max:20',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer',
            'items.*.name'          => 'required|string',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.price'         => 'required|numeric|min:0',
            'total'                 => 'required|numeric|min:0',
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'email'                 => 'required|email|max:255',
            'delivery_address'      => 'required|string|max:500',
            'delivery_instructions' => 'nullable|string|max:1000',
            'delivery_lat'          => 'nullable|numeric',
            'delivery_lng'          => 'nullable|numeric',
            'delivery_place_id'     => 'nullable|string|max:255',
        ]);

        $user  = Auth::user();
        $phone = preg_replace('/\D/', '', $request->phone);
        if (str_starts_with($phone, '0')) $phone = '254' . substr($phone, 1);
        $total = (float) $request->total;
        $ulid  = strtolower((string) \Illuminate\Support\Str::ulid());

        // Get a facility — use wholesale facility linked to the first product's price list
        $firstProductId = $request->items[0]['product_id'] ?? null;
        $facilityId = DB::table('wholesale_price_lists')
            ->where('product_id', $firstProductId)
            ->where('is_active', true)
            ->value('wholesale_facility_id')
            ?? DB::table('facilities')->where('facility_status', 'ACTIVE')->value('id')
            ?? 1;

        DB::beginTransaction();
        try {
            $orderId = DB::table('patient_orders')->insertGetId([
                'ulid'                  => $ulid,
                'patient_phone'         => $phone,
                'patient_name'          => $request->first_name . ' ' . $request->last_name,
                'facility_id'           => $facilityId,
                'status'                => 'PAYMENT_PENDING',
                'subtotal_amount'       => $total,
                'discount_amount'       => 0,
                'total_amount'          => $total,
                'platform_fee_pct'      => 0,
                'platform_fee_amount'   => 0,
                'facility_net_amount'   => $total,
                'customer_first_name'   => $request->first_name,
                'customer_last_name'    => $request->last_name,
                'customer_email'        => $request->email,
                'delivery_address'      => $request->delivery_address,
                'delivery_instructions' => $request->delivery_instructions,
                'delivery_lat'          => $request->delivery_lat,
                'delivery_lng'          => $request->delivery_lng,
                'delivery_place_id'     => $request->delivery_place_id,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            foreach ($request->items as $item) {
                DB::table('patient_order_lines')->insert([
                    'patient_order_id' => $orderId,
                    'product_id'       => $item['product_id'],
                    'quantity'         => $item['qty'],
                    'unit_price'       => $item['price'],
                    'line_discount'    => 0,
                    'line_total'       => $item['qty'] * $item['price'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Patient order failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Order creation failed.'], 500);
        }

        // Trigger M-Pesa STK Push
        try {
            $mpesa = app(\App\Services\Integrations\MpesaDarajaService::class);
            $stkResult = $mpesa->initiateSTKPush($phone, $total, $ulid);

            \Illuminate\Support\Facades\Log::info('Patient STK Push', ['ulid' => $ulid, 'result' => $stkResult]);

            if (isset($stkResult['CheckoutRequestID'])) {
                DB::table('patient_orders')->where('ulid', $ulid)->update([
                    'mpesa_checkout_request_id' => $stkResult['CheckoutRequestID'],
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success'      => true,
                'redirect_url' => "/store/orders/{$ulid}/pending",
                'message'      => 'M-Pesa prompt sent!',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Patient STK failed', ['ulid' => $ulid, 'error' => $e->getMessage()]);
            return response()->json([
                'success'      => true,
                'redirect_url' => "/store/orders/{$ulid}?pending=true",
                'message'      => 'Order placed but M-Pesa prompt failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function paymentPending(string $ulid)
    {
        $user = Auth::user();
        $order = DB::table('patient_orders')
            ->where('ulid', $ulid)
            ->where('patient_phone', 'like', '%' . substr($user->phone ?? '', -9))
            ->first();

        if (! $order) $order = DB::table('patient_orders')->where('ulid', $ulid)->first();
        abort_if(! $order, 404);

        $currency = CurrencyConfig::get();
        return view('store.payment-pending', compact('order', 'currency'));
    }

    public function orderStatus(string $ulid)
    {
        $order = DB::table('patient_orders')->where('ulid', $ulid)->first();
        abort_if(! $order, 404);

        // If still pending and has checkout ID, query Safaricom
        if ($order->status === 'PAYMENT_PENDING' && $order->mpesa_checkout_request_id) {
            try {
                $mpesa = app(\App\Services\Integrations\MpesaDarajaService::class);
                $result = $mpesa->queryTransactionStatus($order->mpesa_checkout_request_id);
                $resultCode = (int) ($result['ResultCode'] ?? -1);

                if ($resultCode === 0) {
                    DB::table('patient_orders')->where('ulid', $ulid)->update([
                        'status' => 'CONFIRMED', 'mpesa_receipt_number' => $result['ResultDesc'] ?? 'CONFIRMED',
                        'paid_at' => now(), 'updated_at' => now(),
                    ]);
                    return response()->json(['paid' => true, 'failed' => false, 'status' => 'CONFIRMED', 'receipt' => $result['ResultDesc'] ?? null, 'redirect_url' => '/store/orders']);
                }

                if (in_array($resultCode, [1, 17, 1032, 1037, 2001])) {
                    $reason = match ($resultCode) {
                        1    => 'Insufficient M-Pesa balance.',
                        17   => 'Transaction limit exceeded.',
                        1032 => 'Payment was cancelled by user.',
                        1037 => 'M-Pesa prompt timed out. Please try again.',
                        2001 => 'Incorrect M-Pesa PIN entered.',
                        default => $result['ResultDesc'] ?? 'Payment failed.',
                    };
                    DB::table('patient_orders')->where('ulid', $ulid)->update([
                        'status' => 'PAYMENT_FAILED', 'rejection_reason' => $reason, 'updated_at' => now(),
                    ]);
                    return response()->json(['paid' => false, 'failed' => true, 'status' => 'PAYMENT_FAILED', 'failure_reason' => $reason]);
                }
            } catch (\Throwable $e) {
                // Query failed — return current DB state, don't crash
                \Illuminate\Support\Facades\Log::warning('STK query failed in orderStatus', ['ulid' => $ulid, 'error' => $e->getMessage()]);
            }

            // Refresh order after possible update
            $order = DB::table('patient_orders')->where('ulid', $ulid)->first();
        }

        $paid   = in_array($order->status, ['CONFIRMED', 'READY', 'COLLECTED']) || ! empty($order->mpesa_receipt_number);
        $failed = $order->status === 'PAYMENT_FAILED';

        return response()->json([
            'paid'           => $paid,
            'failed'         => $failed,
            'status'         => $order->status,
            'receipt'        => $order->mpesa_receipt_number ?? null,
            'failure_reason' => $order->rejection_reason ?? null,
            'redirect_url'   => $paid ? '/store/orders' : null,
        ]);
    }

    public function checkPayment(Request $request, string $ulid)
    {
        $order = DB::table('patient_orders')->where('ulid', $ulid)->first();
        abort_if(! $order, 404);

        // Already confirmed
        if (in_array($order->status, ['CONFIRMED', 'READY', 'COLLECTED'])) {
            return response()->json(['paid' => true, 'failed' => false, 'status' => $order->status, 'receipt' => $order->mpesa_receipt_number]);
        }

        // Already failed (set by callback)
        if ($order->status === 'PAYMENT_FAILED') {
            return response()->json([
                'paid'    => false,
                'failed'  => true,
                'status'  => 'PAYMENT_FAILED',
                'message' => $order->payment_failure_reason ?? $order->rejection_reason ?? 'Payment failed. Please try again.',
            ]);
        }

        if (! $order->mpesa_checkout_request_id) {
            return response()->json(['paid' => false, 'failed' => false, 'status' => $order->status, 'message' => 'Waiting for M-Pesa...']);
        }

        // Query Safaricom directly
        try {
            $mpesa  = app(\App\Services\Integrations\MpesaDarajaService::class);
            $result = $mpesa->queryTransactionStatus($order->mpesa_checkout_request_id);
            $resultCode = (int) ($result['ResultCode'] ?? -1);

            if ($resultCode === 0) {
                DB::table('patient_orders')->where('ulid', $ulid)->update([
                    'status'              => 'CONFIRMED',
                    'mpesa_receipt_number' => $result['ResultDesc'] ?? 'CONFIRMED',
                    'paid_at'             => now(),
                    'updated_at'          => now(),
                ]);
                return response()->json(['paid' => true, 'failed' => false, 'status' => 'CONFIRMED']);
            }

            $failReason = match ($resultCode) {
                1    => 'Insufficient M-Pesa balance.',
                17   => 'M-Pesa transaction limit exceeded.',
                1032 => 'Payment was cancelled.',
                1037 => 'M-Pesa prompt timed out.',
                2001 => 'Incorrect M-Pesa PIN.',
                default => $result['ResultDesc'] ?? 'Payment not yet confirmed.',
            };

            // Permanent failures — update order status
            if (in_array($resultCode, [1, 17, 1032, 2001])) {
                DB::table('patient_orders')->where('ulid', $ulid)->update([
                    'status'           => 'PAYMENT_FAILED',
                    'rejection_reason' => $failReason,
                    'updated_at'       => now(),
                ]);
                return response()->json(['paid' => false, 'failed' => true, 'status' => 'PAYMENT_FAILED', 'message' => $failReason]);
            }

            return response()->json(['paid' => false, 'failed' => false, 'status' => $order->status, 'message' => $failReason]);

        } catch (\Throwable $e) {
            return response()->json(['paid' => false, 'failed' => false, 'status' => $order->status, 'message' => 'Checking payment status...']);
        }
    }

    public function storefront(string $facilityUlid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')
            ->where('ulid', $facilityUlid)
            ->where('facility_status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $facility, 404);

        $eligible = DB::table('online_store_eligible_facilities')
            ->where('facility_id', $facility->id)
            ->where('is_active', true)
            ->first();

        abort_if(! $eligible, 404);

        $products = DB::table('facility_stock_status as fss')
            ->join('products as p', 'p.id', '=', 'fss.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.wholesale_facility_id', '=', 'fss.wholesale_facility_id')
                    ->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.is_active', true);
            })
            ->where('fss.wholesale_facility_id', $facility->id)
            ->select([
                'p.id as product_id',
                'p.ulid as product_ulid',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'p.therapeutic_category',
                'wpl.unit_price',
                'fss.stock_status',
            ])
            ->orderBy('p.therapeutic_category')
            ->orderBy('p.generic_name')
            ->get();

        $categories = $products->pluck('therapeutic_category')->filter()->unique()->sort()->values();

        // Load existing basket for this facility
        $basket = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->where('facility_id', $facility->id)
            ->first();

        $basketToken = $basket->session_token ?? null;

        return view('store.storefront', compact(
            'facility', 'eligible', 'products', 'categories', 'currency', 'basketToken', 'user'
        ));
    }

    public function checkout(string $facilityUlid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')
            ->where('ulid', $facilityUlid)
            ->where('facility_status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $facility, 404);

        $basket = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->where('facility_id', $facility->id)
            ->first();

        if (! $basket) {
            return redirect("/store/{$facilityUlid}")->with('error', 'Your basket is empty.');
        }

        $lines = DB::table('patient_basket_lines as pbl')
            ->join('products as p', 'p.id', '=', 'pbl.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) use ($facility) {
                $join->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.wholesale_facility_id', $facility->id)
                    ->where('wpl.is_active', true);
            })
            ->where('pbl.basket_id', $basket->id)
            ->select([
                'p.generic_name', 'p.brand_name', 'p.unit_size',
                'pbl.quantity', 'wpl.unit_price',
            ])
            ->get()
            ->map(function ($row) {
                $row->line_total = (float) $row->unit_price * $row->quantity;
                return $row;
            });

        if ($lines->isEmpty()) {
            return redirect("/store/{$facilityUlid}")->with('error', 'Your basket is empty.');
        }

        $subtotal = $lines->sum('line_total');

        $platformFeePct = (float) (DB::table('system_settings')
            ->where('key', 'platform_fee_pct')
            ->value('value') ?: 3.00);

        $platformFee = round($subtotal * $platformFeePct / 100, 2);
        $total = round($subtotal + $platformFee, 2);

        return view('store.checkout', compact(
            'facility', 'lines', 'subtotal', 'platformFee', 'platformFeePct', 'total', 'currency', 'user', 'basket'
        ));
    }
}
