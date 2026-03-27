<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CreditEngineService;
use App\Services\CurrencyConfig;
use App\Services\OrderPlacementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetailOrdersController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $currency = CurrencyConfig::get();

        $query = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(20)->withQueryString();

        $counts = [
            'pending'    => DB::table('orders')->where('retail_facility_id', $facilityId)->whereNull('deleted_at')->where('status', 'PENDING')->count(),
            'active'     => DB::table('orders')->where('retail_facility_id', $facilityId)->whereNull('deleted_at')->whereIn('status', ['CONFIRMED','PACKED','DISPATCHED'])->count(),
            'delivered'  => DB::table('orders')->where('retail_facility_id', $facilityId)->whereNull('deleted_at')->where('status', 'DELIVERED')->whereMonth('created_at', now()->month)->count(),
        ];

        return view('retail.orders', compact('orders', 'currency', 'counts'));
    }

    public function show(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $currency = CurrencyConfig::get();

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $order, 404);

        $lines = DB::table('order_lines as ol')
            ->join('products as p', 'ol.product_id', '=', 'p.id')
            ->join('facilities as wf', 'ol.wholesale_facility_id', '=', 'wf.id')
            ->where('ol.order_id', $order->id)
            ->select(['ol.*', 'p.generic_name', 'p.brand_name', 'p.sku_code', 'p.unit_size', 'wf.facility_name as supplier_name'])
            ->get();

        $courier = DB::table('courier_assignments')
            ->where('order_id', $order->id)
            ->first();

        $delivery = DB::table('delivery_confirmations')
            ->where('order_id', $order->id)
            ->first();

        $dispute = DB::table('delivery_disputes')
            ->where('delivery_confirmation_id', $delivery->id ?? 0)
            ->first();

        // Can raise dispute: delivered within 48h and no existing dispute
        $canDispute = $order->status === 'DELIVERED'
            && $delivery
            && $delivery->delivered_at
            && Carbon::parse($delivery->delivered_at)->addHours(48)->isFuture()
            && ! $dispute;

        return view('retail.order-show', compact('order', 'lines', 'currency', 'courier', 'delivery', 'dispute', 'canDispute'));
    }

    public function basket()
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')->where('id', $facilityId)->first();

        // Credit position
        $creditAvailable = 0;
        $creditAccount = DB::table('facility_credit_accounts')
            ->where('facility_id', $facilityId)
            ->where('account_status', 'ACTIVE')
            ->first();

        if ($creditAccount) {
            $balances = DB::table('facility_tranche_balances')
                ->where('credit_account_id', $creditAccount->id)
                ->get();
            $creditAvailable = $balances->sum('available_amount');
        }

        $isNetworkMember = ($facility->network_membership ?? '') === 'NETWORK';

        return view('retail.basket', compact('currency', 'facility', 'creditAvailable', 'isNetworkMember'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        abort_unless($user->hasRole('retail_facility'), 403);

        $facility = DB::table('facilities')->where('id', $facilityId)->first();

        if (! $facility || $facility->facility_status !== 'ACTIVE') {
            return back()->with('error', 'Your account is not active. Contact support.');
        }

        if ($facility->ppb_licence_status === 'EXPIRED') {
            return back()->with('error', 'Your PPB licence has expired. Renew before placing orders.');
        }

        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer',
            'items.*.price_list_id' => 'required|integer',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.payment_type'  => 'required|in:CREDIT,CASH,OFF_NETWORK_CASH',
            'order_type'            => 'required|in:STANDARD,LPO',
            'notes'                 => 'nullable|string|max:1000',
            'mpesa_phone'           => 'nullable|string|max:20',
            'first_name'            => 'nullable|string|max:100',
            'last_name'             => 'nullable|string|max:100',
            'email'                 => 'nullable|email|max:255',
            'delivery_address'      => 'nullable|string|max:500',
            'delivery_instructions' => 'nullable|string|max:1000',
        ]);

        // Normalise M-Pesa phone to 254XXXXXXXXX
        $mpesaPhone = $request->mpesa_phone ? preg_replace('/\s+/', '', $request->mpesa_phone) : null;
        if ($mpesaPhone) {
            if (str_starts_with($mpesaPhone, '+')) $mpesaPhone = substr($mpesaPhone, 1);
            if (str_starts_with($mpesaPhone, '0')) $mpesaPhone = '254' . substr($mpesaPhone, 1);
        }

        // Build lines for OrderPlacementService
        $lines = collect($request->items)->map(fn($item) => [
            'product_id'          => $item['product_id'],
            'price_list_id'       => $item['price_list_id'],
            'quantity'            => $item['quantity'],
            'payment_type'        => $item['payment_type'],
            'tranche_id'          => $item['tranche_id'] ?? null,
            'tier_id'             => $item['tier_id'] ?? null,
            'delivery_facility_id'=> $item['delivery_facility_id'] ?? null,
        ])->toArray();

        try {
            $result = app(OrderPlacementService::class)->placeOrder(
                facilityId: $facilityId,
                placedByUserId: $user->id,
                lines: $lines,
                orderType: $request->order_type,
                sourceChannel: 'WEB',
                notes: $request->notes,
            );

            // Store delivery details on the order
            $deliveryUpdate = array_filter([
                'customer_first_name'   => $request->first_name,
                'customer_last_name'    => $request->last_name,
                'customer_email'        => $request->email,
                'delivery_address'      => $request->delivery_address,
                'delivery_instructions' => $request->delivery_instructions,
                'delivery_lat'          => $request->delivery_lat,
                'delivery_lng'          => $request->delivery_lng,
                'delivery_place_id'     => $request->delivery_place_id,
                'mpesa_phone'           => $mpesaPhone,
            ]);
            if (! empty($deliveryUpdate)) {
                DB::table('orders')->where('id', $result['order_id'])->update($deliveryUpdate);
            }

            DB::table('audit_logs')->insert([
                'facility_id'    => $facilityId,
                'user_id'        => $user->id,
                'action'         => 'ORDER_PLACED',
                'model_type'     => 'App\Models\Order',
                'model_id'       => $result['order_id'],
                'payload_after'  => json_encode(['ulid' => $result['ulid'], 'total' => $result['total_amount']]),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'created_at'     => now(),
            ]);

            try {
                if ($facility->phone) {
                    $this->sendSms($facility->phone,
                        "Your order " . substr($result['ulid'], -8) . " for KES " . number_format($result['total_amount'], 2) . " has been placed and is awaiting confirmation from NILA."
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('SMS failed after order placement', ['error' => $e->getMessage()]);
            }

            $isJson = $request->expectsJson() || $request->isJson();
            $redirectUrl = "/retail/orders/{$result['ulid']}";
            $stkSent = false;

            // Trigger M-Pesa STK Push if phone provided
            if ($mpesaPhone && $result['total_amount'] > 0) {
                try {
                    Log::info('STK Push attempt', [
                        'phone' => $mpesaPhone,
                        'amount' => (int) ceil($result['total_amount']),
                        'ref' => $result['ulid'],
                    ]);

                    $mpesa = app(\App\Services\Integrations\MpesaDarajaService::class);
                    $stkResult = $mpesa->initiateSTKPush(
                        $mpesaPhone,
                        (float) $result['total_amount'],
                        $result['ulid']
                    );

                    Log::info('STK Push result', ['result' => $stkResult]);

                    DB::table('orders')->where('id', $result['order_id'])->update(array_filter([
                        'mpesa_checkout_request_id'  => $stkResult['CheckoutRequestID'] ?? null,
                        'mpesa_merchant_request_id'  => $stkResult['MerchantRequestID'] ?? null,
                        'copay_status'               => 'PENDING',
                    ]));

                    $redirectUrl = "/retail/orders/{$result['ulid']}/payment-pending";
                    $stkSent = true;

                } catch (\Throwable $e) {
                    Log::error('STK Push failed', ['order' => $result['ulid'], 'error' => $e->getMessage()]);
                }
            }

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'redirect' => $redirectUrl,
                    'order_ulid' => $result['ulid'],
                    'stk_sent' => $stkSent,
                    'message' => $stkSent ? 'Order placed! Check your phone for M-Pesa prompt.' : 'Order placed successfully!',
                ]);
            }

            return redirect($redirectUrl)->with('success',
                $stkSent ? 'Order placed! Check your phone for M-Pesa prompt.' : "Order placed! Reference: " . substr($result['ulid'], -8));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if ($request->expectsJson()) return response()->json(['message' => $e->getMessage()], 403);
            return back()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) return response()->json(['message' => $e->getMessage()], 422);
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Order placement failed', ['error' => $e->getMessage()]);
            if ($request->expectsJson()) return response()->json(['message' => 'Order could not be placed. Please try again.'], 500);
            return back()->with('error', 'Order could not be placed. Please try again.');
        }
    }

    public function paymentPending(string $ulid)
    {
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $facilityId = Auth::user()->facility_id;

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $order, 404);

        return view('retail.payment-pending', compact('order'));
    }

    public function paymentStatus(string $ulid)
    {
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);
        $facilityId = Auth::user()->facility_id;

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $order, 404);

        if ($order->status === 'PAYMENT_PENDING') {
            $updated = false;

            // 1) Try Safaricom STK Query (single attempt, 10s timeout, no retry)
            if ($order->mpesa_checkout_request_id) {
                $mpesa  = app(\App\Services\Integrations\MpesaDarajaService::class);
                $result = $mpesa->queryTransactionStatusOnce($order->mpesa_checkout_request_id);

                if ($result !== null) {
                    $resultCode = (int) ($result['ResultCode'] ?? -1);

                    if ($resultCode === 0) {
                        DB::table('orders')->where('ulid', $ulid)->update([
                            'status'        => 'CONFIRMED',
                            'copay_status'  => 'PAID',
                            'mpesa_paid_at' => now(),
                            'paid_at'       => now(),
                            'updated_at'    => now(),
                        ]);
                        $updated = true;
                    } elseif ($resultCode !== -1) {
                        $reason = match ($resultCode) {
                            1    => 'Insufficient M-Pesa balance.',
                            17   => 'Transaction limit exceeded.',
                            1032 => 'Payment was cancelled by user.',
                            1037 => 'M-Pesa prompt timed out. Please try again.',
                            2001 => 'Incorrect M-Pesa PIN entered.',
                            default => $result['ResultDesc'] ?? 'Payment failed.',
                        };
                        DB::table('orders')->where('ulid', $ulid)->update([
                            'status'                 => 'PAYMENT_FAILED',
                            'copay_status'           => 'FAILED',
                            'payment_failure_reason'  => $reason,
                            'failed_at'              => now(),
                            'updated_at'             => now(),
                        ]);
                        $updated = true;
                    }
                }
            }

            // 2) Timeout fallback: M-Pesa STK prompt expires after ~60s.
            if (! $updated) {
                $age = \Carbon\Carbon::parse($order->created_at)->diffInSeconds(now());
                if ($age > 120) {
                    DB::table('orders')->where('ulid', $ulid)->update([
                        'status'                 => 'PAYMENT_FAILED',
                        'copay_status'           => 'FAILED',
                        'payment_failure_reason'  => 'M-Pesa prompt timed out. Please try again.',
                        'failed_at'              => now(),
                        'updated_at'             => now(),
                    ]);
                    $updated = true;
                }
            }

            if ($updated) {
                $order = DB::table('orders')->where('ulid', $ulid)->first();
            }
        }

        $paid   = $order->mpesa_receipt_number !== null
                || in_array($order->status, ['CONFIRMED', 'PACKED', 'DISPATCHED', 'DELIVERED']);
        $failed = $order->copay_status === 'FAILED' || $order->status === 'PAYMENT_FAILED';

        return response()->json([
            'paid'           => $paid,
            'failed'         => $failed,
            'status'         => $order->status,
            'receipt'        => $order->mpesa_receipt_number,
            'failure_reason' => $order->payment_failure_reason,
            'redirect_url'   => $paid ? "/retail/orders/{$ulid}" : null,
        ]);
    }

    public function raiseDispute(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('retail_facility'), 403);

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $facilityId)
            ->where('status', 'DELIVERED')
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $order, 404);

        $delivery = DB::table('delivery_confirmations')
            ->where('order_id', $order->id)
            ->first();

        if (! $delivery || ! $delivery->delivered_at) {
            return back()->with('error', 'No delivery confirmation found for this order.');
        }

        if (Carbon::parse($delivery->delivered_at)->addHours(48)->isPast()) {
            return back()->with('error', 'Dispute window has expired (48 hours after delivery).');
        }

        $existingDispute = DB::table('delivery_disputes')
            ->where('delivery_confirmation_id', $delivery->id)
            ->exists();

        if ($existingDispute) {
            return back()->with('error', 'A dispute has already been raised for this order.');
        }

        $request->validate([
            'reason'      => 'required|in:MISSING_ITEMS,DAMAGED_GOODS,WRONG_ITEMS,SHORT_DELIVERY,OTHER',
            'notes'       => 'required|string|min:20|max:2000',
        ]);

        DB::transaction(function () use ($delivery, $request, $facilityId) {
            DB::table('delivery_disputes')->insert([
                'delivery_confirmation_id' => $delivery->id,
                'raised_by'       => Auth::id(),
                'raised_at'       => now(),
                'reason'          => $request->reason,
                'notes'           => $request->notes,
                'status'          => 'OPEN',
                'sla_deadline_at' => now()->addHours(24),
                'sla_breached'    => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('audit_logs')->insert([
                'facility_id'    => $facilityId,
                'user_id'        => Auth::id(),
                'action'         => 'DISPUTE_RAISED',
                'model_type'     => 'App\Models\Order',
                'model_id'       => DB::table('orders')->where('id', $delivery->order_id)->value('id'),
                'payload_after'  => json_encode(['reason' => $request->reason]),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'created_at'     => now(),
            ]);
        });

        return back()->with('success', 'Dispute raised successfully. Our team will respond within 24 hours.');
    }

    private function sendSms(string $phone, string $message): void
    {
        if (class_exists(\AfricasTalking\SDK::class)) {
            $at = new \AfricasTalking\SDK(config('services.africastalking.username'), config('services.africastalking.api_key'));
            $at->sms()->send(['to' => $phone, 'message' => $message]);
        }
    }
}
