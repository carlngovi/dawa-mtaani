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

    /**
     * Handle M-Pesa callback for retail B2B orders
     * POST /api/payments/mpesa/callback
     */
    public function copayCallback(Request $request): Response
    {
        Log::info('M-Pesa Retail Callback Received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);

        $payload = $request->all();
        $callback = $payload['Body']['stkCallback'] ?? [];

        if (empty($callback)) {
            Log::error('Invalid M-Pesa callback structure - missing stkCallback');
            return response(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback structure'], 400);
        }

        $resultCode = (int) ($callback['ResultCode'] ?? 1);
        $resultDesc = $callback['ResultDesc'] ?? 'Unknown error';
        $checkoutId = $callback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $callback['MerchantRequestID'] ?? null;

        $meta = collect($callback['CallbackMetadata']['Item'] ?? []);
        $amount = $meta->firstWhere('Name', 'Amount')['Value'] ?? null;
        $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
        $phone = $meta->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;

        // Map M-Pesa error codes to user-friendly messages
        $failReason = match ($resultCode) {
            0 => null,
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

        $outcome = $failReason
            ? "FAILED | {$failReason}"
            : "SUCCESS | Receipt: {$receipt}, KES {$amount}, Phone: {$phone}";

        Log::info("MpesaCallback | {$checkoutId} | {$outcome}");

        // =================================================================
        // 1. Update patient_orders (B2C orders)
        // =================================================================
        $patientOrder = null;
        if ($checkoutId) {
            $patientOrder = DB::table('patient_orders')
                ->where('mpesa_checkout_request_id', $checkoutId)
                ->first();
        }
        if (! $patientOrder && $merchantRequestId) {
            $patientOrder = DB::table('patient_orders')
                ->where('mpesa_merchant_request_id', $merchantRequestId)
                ->first();
        }

        if ($patientOrder) {
            if ($resultCode === 0 && $receipt) {
                // Check for duplicate receipt
                $duplicate = DB::table('patient_orders')
                    ->where('mpesa_receipt_number', $receipt)
                    ->where('id', '!=', $patientOrder->id)
                    ->exists();
                
                if ($duplicate) {
                    Log::warning('Duplicate receipt detected for patient order', [
                        'receipt' => $receipt,
                        'order_id' => $patientOrder->id
                    ]);
                } else {
                    DB::table('patient_orders')->where('id', $patientOrder->id)->update([
                        'status'                => 'CONFIRMED',
                        'mpesa_receipt_number'  => $receipt,
                        'paid_at'               => now(),
                        'mpesa_paid_at'         => now(),
                        'mpesa_amount'          => $amount,
                        'mpesa_phone'           => $phone,
                        'mpesa_result_desc'     => $resultDesc,
                        'updated_at'            => now(),
                    ]);
                    
                    Log::info('Patient order confirmed', [
                        'order_id' => $patientOrder->id,
                        'receipt' => $receipt
                    ]);
                }
            } else {
                DB::table('patient_orders')->where('id', $patientOrder->id)->update([
                    'status'                 => 'PAYMENT_FAILED',
                    'rejection_reason'       => $failReason,
                    'payment_failure_reason' => $failReason,
                    'mpesa_result_code'      => $resultCode,
                    'mpesa_result_desc'      => $resultDesc,
                    'failed_at'              => now(),
                    'updated_at'             => now(),
                ]);
                
                Log::warning('Patient order payment failed', [
                    'order_id' => $patientOrder->id,
                    'reason' => $failReason,
                    'result_code' => $resultCode
                ]);
            }
        }

        // =================================================================
        // 2. Update B2B orders (retail/wholesale orders)
        // =================================================================
        $order = null;
        if ($checkoutId) {
            $order = DB::table('orders')
                ->where('mpesa_checkout_request_id', $checkoutId)
                ->first();
        }
        if (! $order && $merchantRequestId) {
            $order = DB::table('orders')
                ->where('mpesa_merchant_request_id', $merchantRequestId)
                ->first();
        }

        if ($order) {
            if ($resultCode === 0 && $receipt) {
                // Check for duplicate receipt
                $duplicate = DB::table('orders')
                    ->where('mpesa_receipt_number', $receipt)
                    ->where('id', '!=', $order->id)
                    ->exists();
                
                if ($duplicate) {
                    Log::warning('Duplicate receipt detected for B2B order', [
                        'receipt' => $receipt,
                        'order_id' => $order->id
                    ]);
                } else {
                    DB::table('orders')->where('id', $order->id)->update([
                        'status'                => 'CONFIRMED',
                        'mpesa_receipt_number'  => $receipt,
                        'mpesa_paid_at'         => now(),
                        'mpesa_amount'          => $amount,
                        'mpesa_phone'           => $phone,
                        'copay_status'          => 'PAID',
                        'paid_at'               => now(),
                        'updated_at'            => now(),
                    ]);
                    
                    Log::info('B2B order confirmed', [
                        'order_id' => $order->id,
                        'receipt' => $receipt
                    ]);
                }
            } else {
                DB::table('orders')->where('id', $order->id)->update([
                    'copay_status'           => 'FAILED',
                    'payment_failure_reason' => $failReason,
                    'mpesa_result_code'      => $resultCode,
                    'mpesa_result_desc'      => $resultDesc,
                    'failed_at'              => now(),
                    'updated_at'             => now(),
                ]);
                
                Log::warning('B2B order payment failed', [
                    'order_id' => $order->id,
                    'reason' => $failReason,
                    'result_code' => $resultCode
                ]);
            }
        }

        // =================================================================
        // 3. Handle copay_payment_attempts (existing B2B flow)
        // =================================================================
        try {
            $this->repaymentService->handleCopayCallback($payload);
        } catch (\Throwable $e) {
            Log::error('Copay callback handler error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }

    /**
     * Handle M-Pesa repayment callback
     * POST /api/payments/mpesa/repayment-callback
     */
    public function repaymentCallback(Request $request): Response
    {
        $payload = $request->all();

        Log::info('MpesaCallback: repayment callback received', [
            'checkout_request_id' => data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            'result_code'         => data_get($payload, 'Body.stkCallback.ResultCode'),
        ]);

        try {
            $this->repaymentService->handleRepaymentCallback($payload);
        } catch (\Throwable $e) {
            Log::error('Repayment callback handler error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }
}