<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessMetricCollector
{
    public static function record(
        string $metricName,
        float $value,
        ?string $county = null,
        ?string $segment = null
    ): void {
        try {
            dispatch(function () use ($metricName, $value, $county, $segment) {
                $now = Carbon::now('UTC');
                $windowStart = $now->copy()->floorMinutes(15);
                $windowEnd = $windowStart->copy()->addMinutes(15);

                $existing = DB::table('business_metric_snapshots')
                    ->where('metric_name', $metricName)
                    ->where('window_start', $windowStart)
                    ->where('county', $county)
                    ->where('segment', $segment)
                    ->first();

                if ($existing) {
                    DB::table('business_metric_snapshots')
                        ->where('id', $existing->id)
                        ->update([
                            'metric_value' => $existing->metric_value + $value,
                        ]);
                } else {
                    DB::table('business_metric_snapshots')->insert([
                        'metric_name'  => $metricName,
                        'metric_value' => $value,
                        'county'       => $county,
                        'segment'      => $segment,
                        'window_start' => $windowStart,
                        'window_end'   => $windowEnd,
                        'created_at'   => $now,
                    ]);
                }
            })->onQueue('default');
        } catch (\Throwable $e) {
            Log::warning('BusinessMetricCollector: failed to dispatch metric', [
                'metric' => $metricName,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public static function recordMany(array $metrics): void
    {
        foreach ($metrics as $metric) {
            self::record(
                metricName: $metric['name'],
                value: $metric['value'],
                county: $metric['county'] ?? null,
                segment: $metric['segment'] ?? null,
            );
        }
    }
}
