<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CopayFailedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private readonly int $orderId) {}

    public function handle(): void
    {
        $order = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('o.id', $this->orderId)
            ->select(
                'o.id',
                'o.ulid',
                'o.copay_status',
                'f.phone as facility_phone',
                'f.name as facility_name'
            )
            ->first();

        if (! $order) {
            Log::warning('CopayFailedNotification: order not found', ['order_id' => $this->orderId]);
            return;
        }

        // Get the latest failed attempt for the failure reason
        $lattempt = DB::table('copay_payment_attempts')
            ->where('order_id', $this->orderId)
            ->orderByDesc('initiated_at')
            ->first();

        $failureReason = match ($lattempt?->mpesa_result_code) {
            '1'    => 'Insufficient funds.',
            '17'   => 'M-Pesa limit reached.',
            '1032' => 'Request cancelled by user.',
            '2001' => 'Wrong PIN entered.',
            default => 'Payment was not completed.',
        };

        dispatch(new SendWhatsAppMessage(
            phone: $order->facility_phone,
            template: 'COPAY_FAILED',
            variables: [
                'order_ref'      => $order->ulid,
                'failure_reason' => $failureReason,
                'retry_url'      => config('app.url') . '/retail/orders/' . $order->ulid,
            ],
        ));
    }
}
