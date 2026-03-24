<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaselineCalculationJob extends MonitoredJob
{
    public function execute(): void
    {
        $processed = 0;
        $cutoff = Carbon::now('UTC')->subDays(14);

        $metrics = DB::table('business_metric_snapshots')
            ->where('window_start', '>=', $cutoff)
            ->select('metric_name', 'county')
            ->distinct()
            ->get();

        foreach ($metrics as $metric) {
            $snapshots = DB::table('business_metric_snapshots')
                ->where('metric_name', $metric->metric_name)
                ->where('county', $metric->county)
                ->where('window_start', '>=', $cutoff)
                ->get();

            $grouped = [];
            foreach ($snapshots as $snap) {
                $dt = Carbon::parse($snap->window_start, 'UTC');
                $key = $dt->dayOfWeek . ':' . $dt->hour;
                $grouped[$key][] = $snap->metric_value;
            }

            foreach ($grouped as $key => $values) {
                [$dow, $hour] = explode(':', $key);
                $avg = array_sum($values) / count($values);

                DB::table('business_metric_baselines')->upsert([
                    [
                        'metric_name'          => $metric->metric_name,
                        'day_of_week'          => (int) $dow,
                        'hour_of_day'          => (int) $hour,
                        'county'               => $metric->county,
                        'baseline_value'       => round($avg, 2),
                        'sample_count'         => count($values),
                        'last_recalculated_at' => Carbon::now('UTC'),
                    ],
                ], ['metric_name', 'day_of_week', 'hour_of_day', 'county'],
                   ['baseline_value', 'sample_count', 'last_recalculated_at']);

                $processed++;
            }
        }

        $this->completed($processed);
    }
}
