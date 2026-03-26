<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Integrations\MpesaDarajaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetryPaymentController extends Controller
{
    public function __construct(
        private readonly MpesaDarajaService $daraja,
    ) {}

    // POST /api/payments/retry/{orderUlid}
    public function retry(Request $request, string $orderUlid): JsonResponse
    {
        $order = Order::where('ulid', $orderUlid)
            ->where('retail_facility_id', $request->user()->facility_id)
            ->firstOrFail();

        // Only FAILED orders can be retried
        if (! in_array($order->copay_status, ['FAILED', 'ESCALATED'])) {
            return response()->json([
                'message' => 'This order is not eligible for a co-pay retry.',
            ], 422);
        }

        // Lifetime retry limit (default 20)
        $lifetimeAttempts = DB::table('copay_payment_attempts')
            ->where('order_id', $order->id)
            ->count();

        $lifetimeLimit = (int) config('system.copay_max_lifetime_retries', 20);

        if ($lifetimeAttempts >= $lifetimeLimit) {
            $order->update(['copay_status' => 'ESCALATED', 'copay_escalated_at' => now()]);

            return response()->json([
                'message' => 'Maximum retry attempts reached for this order. Your order has been escalated to the network administrator for assistance.',
            ], 422);
        }

        // 24-hour rolling rate limit (default 5)
        $recentAttempts = DB::table('copay_payment_attempts')
            ->where('order_id', $order->id)
            ->where('initiated_at', '>=', now()->subHours(24))
            ->count();

        $rollingLimit = (int) config('system.copay_max_retries_per_24h', 5);

        if ($recentAttempts >= $rollingLimit) {
            $order->update(['copay_status' => 'ESCALATED', 'copay_escalated_at' => now()]);

            return response()->json([
                'message' => 'Maximum retry attempts reached for this order. Your order has been escalated to the network administrator for assistance.',
            ], 422);
        }

        // Initiate new STK push
        $attemptNumber = $lifetimeAttempts + 1;

        $result = $this->daraja->initiateSTKPush(
            phone: $order->retailFacility->phone,
            amount: $order->copay_amount,
            reference: $order->ulid,
        );

        $checkoutRequestId = $result['CheckoutRequestID'] ?? null;

        if (! $checkoutRequestId) {
            return response()->json(['message' => 'Failed to initiate payment. Please try again.'], 500);
        }

        // Log the attempt
        DB::table('copay_payment_attempts')->insert([
            'order_id'                  => $order->id,
            'attempt_number'            => $attemptNumber,
            'mpesa_checkout_request_id' => $checkoutRequestId,
            'status'                    => 'INITIATED',
            'initiated_at'              => now(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        $order->update(['copay_status' => 'PENDING']);

        return response()->json([
            'message'             => 'Payment prompt sent to your phone.',
            'checkout_request_id' => $checkoutRequestId,
            'attempt_number'      => $attemptNumber,
        ]);
    }
}
