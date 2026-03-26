<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Integrations\MpesaRepaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function __construct(
        private readonly MpesaRepaymentService $repaymentService,
    ) {}

    // POST /api/payments/mpesa/callback
    public function copayCallback(Request $request): Response
    {
        $payload  = $request->all();
        $callback = $payload['Body']['stkCallback'] ?? [];

        $resultCode = $callback['ResultCode'] ?? null;
        $resultDesc = $callback['ResultDesc'] ?? 'No description';
        $checkoutId = $callback['CheckoutRequestID'] ?? null;

        $meta    = collect($callback['CallbackMetadata']['Item'] ?? []);
        $amount  = $meta->firstWhere('Name', 'Amount')['Value'] ?? null;
        $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
        $phone   = $meta->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;

        $failReason = match ((int) $resultCode) {
            0    => null,
            1    => 'Insufficient M-Pesa balance.',
            17   => 'M-Pesa transaction limit exceeded.',
            1032 => 'Payment was cancelled.',
            1037 => 'M-Pesa prompt timed out. Please try again.',
            2001 => 'Incorrect M-Pesa PIN entered.',
            default => "Payment failed: {$resultDesc}",
        };

        $outcome = $failReason
            ? "FAILED | {$failReason}"
            : "SUCCESS | Receipt: {$receipt}, KES {$amount}, Phone: {$phone}";

        Log::info("MpesaCallback | {$checkoutId} | {$outcome}");

        // Update patient_orders if this checkout ID matches
        $patientOrder = DB::table('patient_orders')
            ->where('mpesa_checkout_request_id', $checkoutId)
            ->first();

        if ($patientOrder) {
            if ((int) $resultCode === 0) {
                DB::table('patient_orders')->where('id', $patientOrder->id)->update([
                    'status'               => 'CONFIRMED',
                    'mpesa_receipt_number'  => $receipt,
                    'paid_at'              => now(),
                    'mpesa_paid_at'        => now(),
                    'updated_at'           => now(),
                ]);
            } else {
                DB::table('patient_orders')->where('id', $patientOrder->id)->update([
                    'status'                 => 'PAYMENT_FAILED',
                    'rejection_reason'       => $failReason,
                    'payment_failure_reason' => $failReason,
                    'updated_at'             => now(),
                ]);
            }
        }

        // Update B2B orders if this checkout ID matches
        $order = DB::table('orders')
            ->where('mpesa_checkout_request_id', $checkoutId)
            ->first();

        if ($order) {
            if ((int) $resultCode === 0) {
                DB::table('orders')->where('id', $order->id)->update([
                    'status'               => 'CONFIRMED',
                    'mpesa_receipt_number'  => $receipt,
                    'mpesa_paid_at'        => now(),
                    'copay_status'         => 'PAID',
                    'updated_at'           => now(),
                ]);
            } else {
                DB::table('orders')->where('id', $order->id)->update([
                    'copay_status'           => 'FAILED',
                    'payment_failure_reason'  => $failReason,
                    'updated_at'             => now(),
                ]);
            }
        }

        // Also handle copay_payment_attempts (existing B2B flow)
        try {
            $this->repaymentService->handleCopayCallback($payload);
        } catch (\Throwable $e) {
            Log::warning('Copay callback handler error: ' . $e->getMessage());
        }

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }

    // POST /api/payments/mpesa/repayment-callback
    public function repaymentCallback(Request $request): Response
    {
        $payload = $request->all();

        Log::info('MpesaCallback: repayment callback received', [
            'checkout_request_id' => data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            'result_code'         => data_get($payload, 'Body.stkCallback.ResultCode'),
        ]);

        $this->repaymentService->handleRepaymentCallback($payload);

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }
}
