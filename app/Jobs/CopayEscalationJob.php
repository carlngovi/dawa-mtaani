<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CopayEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        $timeoutHours = (int) DB::table('system_settings')
            ->where('setting_name', 'copay_escalation_timeout_hours')
            ->value('setting_value') ?? 24;

        $lifetimeLimit = (int) DB::table('system_settings')
            ->where('setting_name', 'copay_max_lifetime_retries')
            ->value('setting_value') ?? 20;

        $cutoff = now()->subHours($timeoutHours);

        // Find FAILED orders whose last attempt is older than the timeout
        $ordersToEscalate = DB::table('orders as o')
            ->join(
                DB::raw('(SELECT order_id, MAX(initiated_at) as last_attempt FROM copay_payment_attempts GROUP BY order_id) as latest'),
                'o.id', '=', 'latest.order_id'
            )
            ->where('o.copay_status', 'FAILED')
            ->where('latest.last_attempt', '<=', $cutoff)
            ->select('o.id', 'o.ulid', 'o.retail_facility_id', 'latest.last_attempt')
            ->get();

        // Also find orders that have hit the lifetime retry limit
        $ordersAtLifetimeLimit = DB::table('orders as o')
            ->join(
                DB::raw('(SELECT order_id, COUNT(*) as total_attempts FROM copay_payment_attempts GROUP BY order_id) as counts'),
                'o.id', '=', 'counts.order_id'
            )
            ->where('o.copay_status', 'FAILED')
            ->where('counts.total_attempts', '>=', $lifetimeLimit)
            ->select('o.id', 'o.ulid', 'o.retail_facility_id')
            ->get();

        $allIds = $ordersToEscalate->pluck('id')
            ->merge($ordersAtLifetimeLimit->pluck('id'))
            ->unique();

        if ($allIds->isEmpty()) {
            return;
        }

        // Escalate all matching orders
        DB::table('orders')
            ->whereIn('id', $allIds)
            ->where('copay_status', 'FAILED')
            ->update([
                'copay_status'       => 'ESCALATED',
                'copay_escalated_at' => now(),
                'updated_at'         => now(),
            ]);

        Log::info('CopayEscalationJob: escalated orders', [
            'count'    => $allIds->count(),
            'order_ids' => $allIds->values(),
        ]);

        // Notify Network admin for each escalated order
        foreach ($allIds as $orderId) {
            dispatch(new \App\Jobs\NotifyAdminCopayEscalated($orderId));
        }
    }
}
