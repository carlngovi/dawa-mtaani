<?php

namespace App\Jobs;

use App\Services\JobAlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionJob extends MonitoredJob
{
    public function execute(): void
    {
        $processed = 0;

        $threshold = (float) (DB::table('system_settings')
            ->where('key', 'anomaly_deviation_threshold_pct')
            ->value('value') ?? 50);

        $now = Carbon::now('UTC');
        $windowStart = $now->copy()->floorMinutes(15)->subMinutes(15);
        $windowEnd = $windowStart->copy()->addMinutes(15);

        $snapshots = DB::table('business_metric_snapshots')
            ->where('window_start', $windowStart)
            ->get();

        foreach ($snapshots as $snap) {
            $baseline = DB::table('business_metric_baselines')
                ->where('metric_name', $snap->metric_name)
                ->where('day_of_week', $windowStart->dayOfWeek)
                ->where('hour_of_day', $windowStart->hour)
                ->where('county', $snap->county)
                ->first();

            if (! $baseline || $baseline->baseline_value == 0) {
                continue;
            }

            $deviationPct = abs(
                (($snap->metric_value - $baseline->baseline_value)
                / $baseline->baseline_value) * 100
            );

            if ($deviationPct >= $threshold) {
                $severity = $deviationPct >= 80 ? 'CRITICAL' : 'WARNING';

                $exists = DB::table('business_metric_alerts')
                    ->where('metric_name', $snap->metric_name)
                    ->where('county', $snap->county)
                    ->whereNull('acknowledged_at')
                    ->where('created_at', '>=', $now->copy()->subHour())
                    ->exists();

                if (! $exists) {
                    DB::table('business_metric_alerts')->insert([
                        'metric_name'    => $snap->metric_name,
                        'expected_value' => $baseline->baseline_value,
                        'actual_value'   => $snap->metric_value,
                        'deviation_pct'  => round($deviationPct, 2),
                        'county'         => $snap->county,
                        'severity'       => $severity,
                        'created_at'     => $now,
                    ]);

                    $processed++;
                }
            }
        }

        $this->completed($processed);
    }
}
