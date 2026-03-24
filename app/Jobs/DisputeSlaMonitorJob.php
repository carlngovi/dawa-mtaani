<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisputeSlaMonitorJob extends MonitoredJob
{
    public function execute(): void
    {
        $now = Carbon::now('UTC');

        $breachedDisputes = DB::table('delivery_disputes')
            ->where('sla_deadline_at', '<', $now)
            ->where('sla_breached', false)
            ->where('status', '!=', 'RESOLVED')
            ->get();

        if ($breachedDisputes->isEmpty()) {
            $this->completed(0);
            return;
        }

        $processed = 0;

        foreach ($breachedDisputes as $dispute) {
            try {
                DB::table('delivery_disputes')
                    ->where('id', $dispute->id)
                    ->update([
                        'sla_breached' => true,
                        'updated_at'   => $now,
                    ]);

                // Log SLA breach to business metrics
                DB::table('business_metric_alerts')->insert([
                    'metric_name'    => 'dispute_sla_breached',
                    'expected_value' => 0,
                    'actual_value'   => 1,
                    'deviation_pct'  => 100,
                    'severity'       => 'WARNING',
                    'created_at'     => $now,
                ]);

                // Write audit log
                DB::table('audit_logs')->insert([
                    'action'        => 'dispute_sla_breached',
                    'model_type'    => 'DeliveryDispute',
                    'model_id'      => $dispute->id,
                    'payload_after' => json_encode([
                        'sla_deadline_at' => $dispute->sla_deadline_at,
                        'breached_at'     => $now->toISOString(),
                    ]),
                    'ip_address'    => '0.0.0.0',
                    'created_at'    => $now,
                ]);

                Log::warning('DisputeSlaMonitorJob: SLA breached', [
                    'dispute_id'     => $dispute->id,
                    'sla_deadline'   => $dispute->sla_deadline_at,
                ]);

                $processed++;

            } catch (\Throwable $e) {
                Log::error('DisputeSlaMonitorJob: failed for dispute', [
                    'dispute_id' => $dispute->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $this->completed($processed);
    }
}
