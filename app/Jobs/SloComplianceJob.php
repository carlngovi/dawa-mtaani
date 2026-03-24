<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SloComplianceJob extends MonitoredJob
{
    // Spec reference — Module 28 SLO/SLI definitions
    private array $slos = [
        'order_placement_success_rate'    => 99.5,
        'payment_instruction_delivery'    => 99.0,
        'mpesa_stk_success_rate'          => 85.0,
        'offline_sync_success_rate'       => 99.0,
    ];

    public function execute(): void
    {
        $processed = 0;
        $yesterday = Carbon::now('UTC')->subDay()->toDateString();
        $periodStart = Carbon::parse($yesterday)->startOfDay();
        $periodEnd = Carbon::parse($yesterday)->endOfDay();

        foreach ($this->slos as $sliName => $targetPct) {
            $snapshots = DB::table('business_metric_snapshots')
                ->where('metric_name', $sliName)
                ->whereBetween('window_start', [$periodStart, $periodEnd])
                ->get();

            if ($snapshots->isEmpty()) {
                continue;
            }

            $totalEvents = (int) $snapshots->sum('metric_value');
            $successMetric = $sliName . '_success';

            $successSnapshots = DB::table('business_metric_snapshots')
                ->where('metric_name', $successMetric)
                ->whereBetween('window_start', [$periodStart, $periodEnd])
                ->get();

            $successfulEvents = (int) $successSnapshots->sum('metric_value');

            if ($totalEvents === 0) {
                continue;
            }

            $compliancePct = round(($successfulEvents / $totalEvents) * 100, 4);

            DB::table('slo_compliance_records')->insert([
                'sli_name'          => $sliName,
                'period_start'      => $yesterday,
                'period_end'        => $yesterday,
                'total_events'      => $totalEvents,
                'successful_events' => $successfulEvents,
                'compliance_pct'    => $compliancePct,
                'slo_target_pct'    => $targetPct,
                'is_compliant'      => $compliancePct >= $targetPct,
                'created_at'        => Carbon::now('UTC'),
            ]);

            $processed++;
        }

        $this->completed($processed);
    }
}
