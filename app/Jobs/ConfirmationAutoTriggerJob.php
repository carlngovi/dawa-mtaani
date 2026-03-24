<?php

namespace App\Jobs;

use App\Services\BusinessMetricCollector;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfirmationAutoTriggerJob extends MonitoredJob
{
    public function __construct(
        private readonly int $confirmationId
    ) {}

    public function execute(): void
    {
        $now = Carbon::now('UTC');

        $confirmation = DB::table('delivery_confirmations')
            ->where('id', $this->confirmationId)
            ->whereNull('confirmed_at')
            ->first();

        // Already confirmed or disputed — skip
        if (! $confirmation) {
            Log::info('ConfirmationAutoTriggerJob: already confirmed, skipping', [
                'confirmation_id' => $this->confirmationId,
            ]);
            $this->completed(0);
            return;
        }

        // Check deadline has actually passed
        if (Carbon::parse($confirmation->confirmation_deadline_at)->isFuture()) {
            Log::info('ConfirmationAutoTriggerJob: deadline not yet passed, skipping', [
                'confirmation_id' => $this->confirmationId,
            ]);
            $this->completed(0);
            return;
        }

        DB::transaction(function () use ($confirmation, $now) {
            // Mark as auto-triggered
            DB::table('delivery_confirmations')
                ->where('id', $confirmation->id)
                ->update([
                    'confirmed_at'            => $now,
                    'confirmation_type'       => 'AUTO_TRIGGERED',
                    'auto_trigger_fired_at'   => $now,
                    'updated_at'              => $now,
                ]);

            // Update order/split status
            if ($confirmation->order_id) {
                DB::table('orders')
                    ->where('id', $confirmation->order_id)
                    ->update([
                        'status'     => 'DELIVERED',
                        'updated_at' => $now,
                    ]);
            }

            if ($confirmation->split_line_id) {
                DB::table('order_delivery_splits')
                    ->where('id', $confirmation->split_line_id)
                    ->update([
                        'status'       => 'DELIVERED',
                        'confirmed_at' => $now,
                        'updated_at'   => $now,
                    ]);
            }

            // Write audit log
            DB::table('audit_logs')->insert([
                'action'        => 'delivery_auto_confirmed',
                'model_type'    => 'DeliveryConfirmation',
                'model_id'      => $confirmation->id,
                'payload_after' => json_encode([
                    'confirmation_type' => 'AUTO_TRIGGERED',
                    'fired_at'          => $now->toISOString(),
                ]),
                'ip_address'    => '0.0.0.0',
                'created_at'    => $now,
            ]);
        });

        BusinessMetricCollector::record('deliveries_auto_confirmed', 1);

        Log::info('ConfirmationAutoTriggerJob: auto-triggered confirmation', [
            'confirmation_id' => $this->confirmationId,
            'order_id'        => $confirmation->order_id,
            'split_line_id'   => $confirmation->split_line_id,
        ]);

        $this->completed(1);
    }
}
