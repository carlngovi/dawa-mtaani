<?php

namespace App\Http\Controllers\Store;

use App\Events\OrderConfirmed;
use App\Http\Controllers\Controller;
use App\Models\PatientBasket;
use App\Models\PatientOrder;
use App\Models\PatientOrderLine;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Services\CurrencyConfig;
use App\Services\Integrations\MpesaDarajaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'patient_phone' => 'required|string|max:20',
            'patient_name'  => 'nullable|string|max:255',
            'promo_code'    => 'nullable|string|max:50',
        ]);

        $basket = PatientBasket::where('session_token', $validated['session_token'])->first();

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
                $validated['patient_phone'],
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

            $order = PatientOrder::create([
                'ulid'                => strtolower(Str::ulid()),
                'patient_phone'       => $validated['patient_phone'],
                'patient_name'        => $validated['patient_name'] ?? null,
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
                PatientOrderLine::create(array_merge($lineData, [
                    'patient_order_id' => $order->id,
                ]));
            }

            if ($promoCode) {
                PromoCodeUsage::create([
                    'promo_code_id'    => $promoCode->id,
                    'patient_phone'    => $validated['patient_phone'],
                    'patient_order_id' => $order->id,
                    'used_at'          => now(),
                ]);
            }

            $mpesa = app(MpesaDarajaService::class);
            $stkResponse = $mpesa->initiateSTKPush(
                $validated['patient_phone'],
                $total,
                $order->ulid
            );

            $order->update([
                'mpesa_checkout_request_id' => $stkResponse['CheckoutRequestID'] ?? null,
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
                'mpesa_prompt' => "STK push sent to {$validated['patient_phone']}",
            ],
            'message' => '',
        ]);
    }

    public function mpesaCallback(Request $request): JsonResponse
    {
        $callback = $request->input('Body.stkCallback');

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? -1;

        $order = PatientOrder::where('mpesa_checkout_request_id', $checkoutRequestId)->first();

        if (! $order) {
            return response()->json(['status' => 'ignored']);
        }

        if ($order->status !== 'PAYMENT_PENDING') {
            return response()->json(['status' => 'already_processed']);
        }

        if ((int) $resultCode === 0) {
            $receiptNumber = null;
            $items = $callback['CallbackMetadata']['Item'] ?? [];
            foreach ($items as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $receiptNumber = $item['Value'];
                    break;
                }
            }

            // Idempotency: check duplicate receipt
            if ($receiptNumber && PatientOrder::where('mpesa_receipt_number', $receiptNumber)->exists()) {
                return response()->json(['status' => 'duplicate_receipt']);
            }

            DB::transaction(function () use ($order, $receiptNumber) {
                $order->update([
                    'status'               => 'CONFIRMED',
                    'mpesa_receipt_number'  => $receiptNumber,
                    'paid_at'              => now(),
                ]);

                foreach ($order->lines as $line) {
                    DB::table('facility_stock_status')
                        ->where('wholesale_facility_id', $order->facility_id)
                        ->where('product_id', $line->product_id)
                        ->decrement('stock_quantity', $line->quantity);
                }

                event(new OrderConfirmed($order));
            });
        } else {
            $order->update(['status' => 'CANCELLED']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function getOrderStatus(string $ulid): JsonResponse
    {
        $order = PatientOrder::where('ulid', $ulid)->first();

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
                'lines'        => $lines,
            ],
            'message' => '',
        ]);
    }

    public function markCollected(Request $request, string $ulid): JsonResponse
    {
        $order = PatientOrder::where('ulid', $ulid)->first();

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

        // TODO: Dispatch SMS receipt notification

        return response()->json([
            'status'  => 'success',
            'data'    => [],
            'message' => '',
        ]);
    }

    private function validatePromo(string $code, string $phone, PatientBasket $basket): array
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

        if ($promo->usage_cap_per_patient !== null) {
            $patientUsed = PromoCodeUsage::where('promo_code_id', $promo->id)
                ->where('patient_phone', $phone)
                ->count();
            if ($patientUsed >= $promo->usage_cap_per_patient) {
                return [null, 0, 'You have already used this promo code.'];
            }
        }

        // Calculate subtotal to check min_order_value
        $subtotal = (float) DB::table('patient_basket_lines as pbl')
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
            'BUY_X_GET_Y'     => 0, // Deferred to Phase 4
        };
    }
}
