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
                'user_id'               => Auth::id(),
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

            DB::table('patient_orders')->where('ulid', $ulid)->update(array_filter([
                'mpesa_checkout_request_id'  => $stkResult['CheckoutRequestID'] ?? null,
                'mpesa_merchant_request_id'  => $stkResult['MerchantRequestID'] ?? null,
                'updated_at'                 => now(),
            ]));

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
            ->where('user_id', $user->id)
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

        if ($order->status === 'PAYMENT_PENDING') {
            $updated = false;

            // 1) Try Safaricom STK Query (single attempt, 10s timeout, no retry)
            if ($order->mpesa_checkout_request_id) {
                $mpesa  = app(\App\Services\Integrations\MpesaDarajaService::class);
                $result = $mpesa->queryTransactionStatusOnce($order->mpesa_checkout_request_id);

                if ($result !== null) {
                    $resultCode = (int) ($result['ResultCode'] ?? -1);
                    $resultDesc = $result['ResultDesc'] ?? '';

                    // Safaricom STK Query returns 1032 for BOTH "cancelled"
                    // and "still processing". Only treat it as failed when
                    // the description explicitly says cancelled.
                    $stillProcessing = $resultCode === -1
                        || ($resultCode === 1032 && stripos($resultDesc, 'processing') !== false);

                    if ($resultCode === 0) {
                        DB::table('patient_orders')->where('ulid', $ulid)->update([
                            'status'               => 'CONFIRMED',
                            'mpesa_result_code'    => $resultCode,
                            'mpesa_result_desc'    => $resultDesc,
                            'paid_at'              => now(),
                            'updated_at'           => now(),
                        ]);
                        $updated = true;
                    } elseif (! $stillProcessing) {
                        // Definite failure — user cancelled, wrong PIN, etc.
                        $reason = match ($resultCode) {
                            1    => 'Insufficient M-Pesa balance.',
                            17   => 'Transaction limit exceeded.',
                            1032 => 'Payment was cancelled by user.',
                            1037 => 'M-Pesa prompt timed out. Please try again.',
                            2001 => 'Incorrect M-Pesa PIN entered.',
                            default => $resultDesc ?: 'Payment failed.',
                        };
                        DB::table('patient_orders')->where('ulid', $ulid)->update([
                            'status'                 => 'PAYMENT_FAILED',
                            'rejection_reason'       => $reason,
                            'payment_failure_reason'  => $reason,
                            'mpesa_result_code'      => $resultCode,
                            'mpesa_result_desc'      => $resultDesc,
                            'failed_at'              => now(),
                            'updated_at'             => now(),
                        ]);
                        $updated = true;
                    }
                    // else: still processing — leave as PAYMENT_PENDING, let next poll retry
                }
            }

            // 2) Timeout fallback: M-Pesa STK prompt expires after ~60s.
            //    If 2+ minutes have passed and neither callback nor STK Query
            //    resolved it, mark as failed so the user isn't stuck forever.
            if (! $updated) {
                $age = \Carbon\Carbon::parse($order->created_at)->diffInSeconds(now());
                if ($age > 120) {
                    DB::table('patient_orders')->where('ulid', $ulid)->update([
                        'status'                 => 'PAYMENT_FAILED',
                        'rejection_reason'       => 'M-Pesa prompt timed out. Please try again.',
                        'payment_failure_reason'  => 'M-Pesa prompt timed out. Please try again.',
                        'failed_at'              => now(),
                        'updated_at'             => now(),
                    ]);
                    $updated = true;
                }
            }

            if ($updated) {
                $order = DB::table('patient_orders')->where('ulid', $ulid)->first();
            }
        }

        $paid   = in_array($order->status, ['CONFIRMED', 'READY', 'COLLECTED'])
                || ! empty($order->mpesa_receipt_number);
        $failed = $order->status === 'PAYMENT_FAILED';

        return response()->json([
            'paid'           => $paid,
            'failed'         => $failed,
            'status'         => $order->status,
            'receipt'        => $order->mpesa_receipt_number ?? null,
            'failure_reason' => $order->payment_failure_reason ?? $order->rejection_reason ?? null,
            'redirect_url'   => $paid ? '/store/orders' : null,
        ]);
    }

    public function checkPayment(Request $request, string $ulid)
    {
        // Same as orderStatus — just reads from DB.
        // The callback controller handles all Safaricom updates.
        return $this->orderStatus($ulid);
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
