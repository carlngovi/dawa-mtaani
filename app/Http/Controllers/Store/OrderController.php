<?php

namespace App\Http\Controllers\Store;

use App\Events\OrderConfirmed;
use App\Http\Controllers\Controller;
use App\Models\CustomerBasket;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderLine;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Services\CurrencyConfig;
use App\Services\Integrations\MpesaDarajaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'customer_phone' => 'required|string|max:20',
            'customer_name'  => 'nullable|string|max:255',
            'promo_code'    => 'nullable|string|max:50',
        ]);

        $basket = CustomerBasket::where('session_token', $validated['session_token'])->first();

        if (! $basket) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Basket not found.',
            ], 404);
        }

        if (! $basket->reserved_until || $basket->reserved_until->isPast()) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Reservation expired. Please restart checkout.',
            ], 422);
        }

        $basket->load('lines');

        $promoCode = null;
        $discountAmount = 0;
        $promoError = null;

        if (! empty($validated['promo_code'])) {
            [$promoCode, $discountAmount, $promoError] = $this->validatePromo(
                $validated['promo_code'],
                $validated['customer_phone'],
                $basket
            );

            if ($promoError) {
                return response()->json([
                    'status'  => 'error',
                    'data'    => [],
                    'message' => $promoError,
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($basket, $validated, $promoCode, $discountAmount) {
            $subtotal = 0;
            $orderLines = [];

            foreach ($basket->lines as $line) {
                $price = DB::table('wholesale_price_lists')
                    ->where('wholesale_facility_id', $basket->facility_id)
                    ->where('product_id', $line->product_id)
                    ->where('is_active', true)
                    ->value('unit_price');

                $unitPrice = (float) $price;
                $lineTotal = $unitPrice * $line->quantity;
                $subtotal += $lineTotal;

                $orderLines[] = [
                    'product_id'    => $line->product_id,
                    'quantity'      => $line->quantity,
                    'unit_price'    => $unitPrice,
                    'line_discount' => 0,
                    'line_total'    => $lineTotal,
                ];
            }

            // Recalculate discount against actual subtotal
            if ($promoCode) {
                $discountAmount = $this->calculateDiscount($promoCode, $subtotal);
            } else {
                $discountAmount = 0;
            }

            $total = max(0, $subtotal - $discountAmount);

            $platformFeePct = (float) (DB::table('system_settings')
                ->where('key', 'platform_fee_pct')
                ->value('value') ?: 3.00);

            $platformFeeAmount = round($total * $platformFeePct / 100, 2);
            $facilityNet = round($total - $platformFeeAmount, 2);

            $order = CustomerOrder::create([
                'ulid'                => strtolower(Str::ulid()),
                'user_id'             => auth()->id(),
                'customer_phone'       => $validated['customer_phone'],
                'customer_name'        => $validated['customer_name'] ?? null,
                'facility_id'         => $basket->facility_id,
                'status'              => 'PAYMENT_PENDING',
                'subtotal_amount'     => $subtotal,
                'discount_amount'     => $discountAmount,
                'total_amount'        => $total,
                'platform_fee_pct'    => $platformFeePct,
                'platform_fee_amount' => $platformFeeAmount,
                'facility_net_amount' => $facilityNet,
                'promo_code_id'       => $promoCode?->id,
            ]);

            foreach ($orderLines as $lineData) {
                CustomerOrderLine::create(array_merge($lineData, [
                    'customer_order_id' => $order->id,
                ]));
            }

            if ($promoCode) {
                PromoCodeUsage::create([
                    'promo_code_id'    => $promoCode->id,
                    'customer_phone'    => $validated['customer_phone'],
                    'customer_order_id' => $order->id,
                    'used_at'          => now(),
                ]);
            }

            $mpesa = app(MpesaDarajaService::class);
            $stkResponse = $mpesa->initiateSTKPush(
                $validated['customer_phone'],
                $total,
                $order->ulid
            );

            $order->update([
                'mpesa_checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
                'mpesa_merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
            ]);

            $basket->lines()->delete();
            $basket->delete();

            return $order;
        });

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'order_ulid'  => $order->ulid,
                'total'       => CurrencyConfig::format((float) $order->total_amount),
                'mpesa_prompt' => "STK push sent to {$validated['customer_phone']}",
                'checkout_request_id' => $order->mpesa_checkout_request_id,
            ],
            'message' => '',
        ]);
    }

    public function mpesaCallback(Request $request): JsonResponse
    {
        Log::info('M-Pesa Customer Callback Received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);

        $callback = $request->input('Body.stkCallback');
        
        if (!$callback) {
            Log::error('Invalid M-Pesa callback structure - missing stkCallback');
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback structure'], 400);
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $callback['MerchantRequestID'] ?? null;
        $resultCode = (int) ($callback['ResultCode'] ?? 1);
        $resultDesc = $callback['ResultDesc'] ?? 'Unknown error';

        Log::info('Processing M-Pesa callback', [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);

        // Find order by either checkout request ID or merchant request ID
        $order = CustomerOrder::where('mpesa_checkout_request_id', $checkoutRequestId)
            ->orWhere('mpesa_merchant_request_id', $merchantRequestId)
            ->first();

        if (! $order) {
            Log::warning('Order not found for callback', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId
            ]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Order not found, but accepted'], 200);
        }

        // Prevent double processing
        if ($order->status !== 'PAYMENT_PENDING') {
            Log::info('Order already processed', [
                'order_ulid' => $order->ulid,
                'current_status' => $order->status
            ]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Already processed'], 200);
        }

        // Handle successful payment (ResultCode 0)
        if ($resultCode === 0) {
            $receiptNumber = null;
            $amount = null;
            $phone = null;
            
            $items = $callback['CallbackMetadata']['Item'] ?? [];
            foreach ($items as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $receiptNumber = $item['Value'];
                }
                if ($item['Name'] === 'Amount') {
                    $amount = $item['Value'];
                }
                if ($item['Name'] === 'PhoneNumber') {
                    $phone = $item['Value'];
                }
            }

            // Check for duplicate receipt
            if ($receiptNumber && CustomerOrder::where('mpesa_receipt_number', $receiptNumber)->exists()) {
                Log::warning('Duplicate receipt number detected', ['receipt' => $receiptNumber]);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Duplicate receipt'], 200);
            }

            DB::transaction(function () use ($order, $receiptNumber, $amount, $phone, $resultDesc) {
                $order->update([
                    'status'                => 'CONFIRMED',
                    'mpesa_receipt_number'  => $receiptNumber,
                    'mpesa_paid_at'         => now(),
                    'paid_at'               => now(),
                    'mpesa_amount'          => $amount,
                    'mpesa_phone'           => $phone,
                    'mpesa_result_desc'     => $resultDesc,
                ]);

                // Update stock
                foreach ($order->lines as $line) {
                    DB::table('facility_stock_status')
                        ->where('wholesale_facility_id', $order->facility_id)
                        ->where('product_id', $line->product_id)
                        ->decrement('stock_quantity', $line->quantity);
                }

                event(new OrderConfirmed($order));
                
                Log::info('Order confirmed successfully', [
                    'order_ulid' => $order->ulid,
                    'receipt' => $receiptNumber,
                    'amount' => $amount
                ]);
            });
        } 
        // Handle failed payment
        else {
            // Map M-Pesa error codes to user-friendly messages
            $failureReason = match ($resultCode) {
                1 => 'Insufficient M-Pesa balance. Please top up and try again.',
                17 => 'M-Pesa transaction limit exceeded. Please use another payment method.',
                1032 => 'Payment was cancelled by the user.',
                1037 => 'M-Pesa prompt timed out. Please try again.',
                2001 => 'Incorrect M-Pesa PIN entered. Please try again.',
                2002 => 'M-Pesa service is temporarily unavailable. Please try again later.',
                2003 => 'Transaction declined by the bank. Please contact your bank.',
                400 => 'Invalid transaction. Please try again.',
                default => "Payment failed: {$resultDesc}",
            };

            $order->update([
                'status'                 => 'PAYMENT_FAILED',
                'payment_failure_reason' => $failureReason,
                'mpesa_result_code'      => $resultCode,
                'mpesa_result_desc'      => $resultDesc,
                'failed_at'              => now(),
            ]);

            Log::warning('Order payment failed', [
                'order_ulid' => $order->ulid,
                'result_code' => $resultCode,
                'reason' => $failureReason
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }

    public function getOrderStatus(string $ulid): JsonResponse
    {
        $order = CustomerOrder::where('ulid', $ulid)->first();

        if (! $order) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Order not found.',
            ], 404);
        }

        $order->load('lines.product');

        $lines = $order->lines->map(fn ($line) => [
            'product_name' => $line->product->generic_name,
            'quantity'     => $line->quantity,
            'unit_price'   => CurrencyConfig::format((float) $line->unit_price),
            'line_total'   => CurrencyConfig::format((float) $line->line_total),
        ]);

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'order_ulid'   => $order->ulid,
                'status'       => $order->status,
                'paid_at'      => $order->paid_at,
                'collected_at' => $order->collected_at,
                'total_amount' => CurrencyConfig::format((float) $order->total_amount),
                'failure_reason' => $order->payment_failure_reason,
                'lines'        => $lines,
            ],
            'message' => '',
        ]);
    }

    public function markCollected(Request $request, string $ulid): JsonResponse
    {
        $order = CustomerOrder::where('ulid', $ulid)->first();

        if (! $order) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Order not found.',
            ], 404);
        }

        $user = $request->user();

        if (! $user || $user->facility_id !== $order->facility_id) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order->update([
            'status'       => 'COLLECTED',
            'collected_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'data'    => [],
            'message' => 'Order marked as collected',
        ]);
    }

    private function validatePromo(string $code, string $phone, CustomerBasket $basket): array
    {
        $promo = PromoCode::where('code', $code)->first();

        if (! $promo) {
            return [null, 0, 'Invalid promo code.'];
        }

        if ($promo->valid_from->isFuture()) {
            return [null, 0, 'Promo code is not yet active.'];
        }

        if ($promo->valid_until && $promo->valid_until->isPast()) {
            return [null, 0, 'Promo code has expired.'];
        }

        if ($promo->usage_cap_total !== null) {
            $totalUsed = PromoCodeUsage::where('promo_code_id', $promo->id)->count();
            if ($totalUsed >= $promo->usage_cap_total) {
                return [null, 0, 'Promo code usage limit reached.'];
            }
        }

        if ($promo->usage_cap_per_customer !== null) {
            $customerUsed = PromoCodeUsage::where('promo_code_id', $promo->id)
                ->where('customer_phone', $phone)
                ->count();
            if ($customerUsed >= $promo->usage_cap_per_customer) {
                return [null, 0, 'You have already used this promo code.'];
            }
        }

        // Calculate subtotal to check min_order_value
        $subtotal = (float) DB::table('customer_basket_lines as pbl')
            ->join('wholesale_price_lists as wpl', function ($join) use ($basket) {
                $join->on('wpl.product_id', '=', 'pbl.product_id')
                    ->where('wpl.wholesale_facility_id', $basket->facility_id)
                    ->where('wpl.is_active', true);
            })
            ->where('pbl.basket_id', $basket->id)
            ->selectRaw('SUM(wpl.unit_price * pbl.quantity) as subtotal')
            ->value('subtotal') ?? 0;

        if ($promo->min_order_value !== null && $subtotal < (float) $promo->min_order_value) {
            return [null, 0, 'Minimum order value not met.'];
        }

        $discount = $this->calculateDiscount($promo, $subtotal);

        return [$promo, $discount, null];
    }

    private function calculateDiscount(PromoCode $promo, float $subtotal): float
    {
        return match ($promo->discount_type) {
            'PERCENTAGE_OFF'  => round($subtotal * ((float) $promo->discount_value / 100), 2),
            'FIXED_AMOUNT_OFF' => min((float) $promo->discount_value, $subtotal),
            'BUY_X_GET_Y'     => 0,
        };
    }
}