<?php

namespace App\Services\Integrations;

use App\Models\Order;
use App\Models\RepaymentRecord;
use App\Services\CreditEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaRepaymentService
{
    public function __construct(
        private readonly CreditEngineService $creditEngine,
        private readonly MpesaDarajaService  $daraja,
    ) {}

    // -------------------------------------------------------------------------
    // STK PUSH CALLBACK — B2B co-pay (Module 7)
    // -------------------------------------------------------------------------

    public function handleCopayCallback(array $payload): void
    {
        if (! $this->daraja->verifyCallback($payload)) {
            Log::warning('MpesaRepaymentService: invalid callback structure', $payload);
            return;
        }

        $callback          = $payload['Body']['stkCallback'];
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode        = (int) $callback['ResultCode'];

        DB::transaction(function () use ($checkoutRequestId, $resultCode, $callback) {

            // Find the copay attempt by checkout request ID
            $attempt = DB::table('copay_payment_attempts')
                ->where('mpesa_checkout_request_id', $checkoutRequestId)
                ->lockForUpdate()
                ->first();

            if (! $attempt) {
                Log::warning('MpesaRepaymentService: no copay attempt found', [
                    'checkout_request_id' => $checkoutRequestId,
                ]);
                return;
            }

            // Idempotency — already processed
            if (in_array($attempt->status, ['SUCCESS', 'FAILED'])) {
                return;
            }

            $order = Order::lockForUpdate()->findOrFail($attempt->order_id);

            if ($resultCode === 0) {
                // Payment successful
                $mpesaRef = $this->extractMpesaRef($callback);

                DB::table('copay_payment_attempts')
                    ->where('id', $attempt->id)
                    ->update([
                        'status'       => 'SUCCESS',
                        'completed_at' => now(),
                        'mpesa_result_code' => (string) $resultCode,
                    ]);

                $order->update([
                    'copay_status' => 'PAID',
                ]);

                // Trigger repayment processing if a repayment record exists
                $repayment = RepaymentRecord::where('order_id', $order->id)
                    ->where('status', 'PENDING')
                    ->first();

                if ($repayment) {
                    $repayment->update([
                        'amount_paid'      => $repayment->amount_due,
                        'paid_at'          => now(),
                        'mpesa_reference'  => $mpesaRef,
                        'status'           => 'PAID',
                        'days_to_repay'    => (int) $repayment->created_at->diffInDays(now()),
                        'payment_method'   => 'MPESA',
                    ]);

                    $this->creditEngine->processRepayment($repayment->id);
                }

            } else {
                // Payment failed
                $failureReason = $callback['ResultDesc'] ?? 'Unknown failure';

                DB::table('copay_payment_attempts')
                    ->where('id', $attempt->id)
                    ->update([
                        'status'            => 'FAILED',
                        'completed_at'      => now(),
                        'mpesa_result_code' => (string) $resultCode,
                        'failure_reason'    => $failureReason,
                    ]);

                $order->update([
                    'copay_status' => 'FAILED',
                ]);

                // Dispatch WhatsApp failure notification
                dispatch(new \App\Jobs\CopayFailedNotification($order->id));
            }
        });
    }

    // -------------------------------------------------------------------------
    // REPAYMENT CALLBACK — standard repayment collection
    // -------------------------------------------------------------------------

    public function handleRepaymentCallback(array $payload): void
    {
        if (! $this->daraja->verifyCallback($payload)) {
            Log::warning('MpesaRepaymentService: invalid repayment callback structure', $payload);
            return;
        }

        $callback          = $payload['Body']['stkCallback'];
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode        = (int) $callback['ResultCode'];

        if ($resultCode !== 0) {
            Log::info('MpesaRepaymentService: repayment STK declined', [
                'checkout_request_id' => $checkoutRequestId,
                'result_desc'         => $callback['ResultDesc'] ?? '',
            ]);
            return;
        }

        $mpesaRef = $this->extractMpesaRef($callback);

        DB::transaction(function () use ($checkoutRequestId, $mpesaRef, $callback) {
            $repayment = RepaymentRecord::where('mpesa_reference', $checkoutRequestId)
                ->lockForUpdate()
                ->first();

            if (! $repayment) {
                Log::warning('MpesaRepaymentService: no repayment record matched', [
                    'checkout_request_id' => $checkoutRequestId,
                ]);
                return;
            }

            // Idempotency
            if ($repayment->status === 'PAID') {
                return;
            }

            $repayment->update([
                'amount_paid'     => $repayment->amount_due,
                'paid_at'         => now(),
                'mpesa_reference' => $mpesaRef,
                'status'          => 'PAID',
                'days_to_repay'   => (int) $repayment->created_at->diffInDays(now()),
                'payment_method'  => 'MPESA',
            ]);

            $this->creditEngine->processRepayment($repayment->id);
        });
    }

    // -------------------------------------------------------------------------
    // HELPER
    // -------------------------------------------------------------------------

    private function extractMpesaRef(array $callback): ?string
    {
        $items = $callback['CallbackMetadata']['Item'] ?? [];
        foreach ($items as $item) {
            if ($item['Name'] === 'MpesaReceiptNumber') {
                return $item['Value'] ?? null;
            }
        }
        return null;
    }
}
