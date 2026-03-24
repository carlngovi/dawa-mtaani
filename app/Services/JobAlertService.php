<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobAlertService
{
    public function check(): void
    {
        try {
            $this->checkConsecutiveFailures();
        } catch (\Throwable $e) {
            Log::error('JobAlertService: consecutive failure check failed', [
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $this->checkDurationAnomalies();
        } catch (\Throwable $e) {
            Log::error('JobAlertService: duration anomaly check failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function checkConsecutiveFailures(): void
    {
        $jobNames = DB::table('job_monitoring')
            ->distinct()
            ->pluck('job_name');

        foreach ($jobNames as $jobName) {
            $lastThree = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->orderBy('started_at', 'desc')
                ->limit(3)
                ->pluck('status')
                ->toArray();

            if (count($lastThree) === 3 && array_unique($lastThree) === ['FAILED']) {
                $this->insertAlert(
                    metricName: 'job_failure:' . $jobName,
                    expected: 0,
                    actual: 3,
                    deviationPct: 100,
                    severity: 'CRITICAL',
                );

                Log::critical('JobAlertService: 3 consecutive failures', [
                    'job' => $jobName,
                ]);
            }
        }
    }

    private function checkDurationAnomalies(): void
    {
        $jobNames = DB::table('job_monitoring')
            ->distinct()
            ->pluck('job_name');

        $sevenDaysAgo = Carbon::now('UTC')->subDays(7);

        foreach ($jobNames as $jobName) {
            $avgDuration = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->where('status', 'COMPLETED')
                ->where('started_at', '>=', $sevenDaysAgo)
                ->avg('duration_ms');

            if (! $avgDuration) {
                continue;
            }

            $lastDuration = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->where('status', 'COMPLETED')
                ->orderBy('started_at', 'desc')
                ->value('duration_ms');

            if (! $lastDuration) {
                continue;
            }

            if ($lastDuration > ($avgDuration * 2)) {
                $deviationPct = round((($lastDuration - $avgDuration) / $avgDuration) * 100, 2);

                $this->insertAlert(
                    metricName: 'job_duration:' . $jobName,
                    expected: $avgDuration,
                    actual: $lastDuration,
                    deviationPct: $deviationPct,
                    severity: 'WARNING',
                );
            }
        }
    }

    private function insertAlert(
        string $metricName,
        float $expected,
        float $actual,
        float $deviationPct,
        string $severity,
    ): void {
        $oneHourAgo = Carbon::now('UTC')->subHour();

        $exists = DB::table('business_metric_alerts')
            ->where('metric_name', $metricName)
            ->whereNull('acknowledged_at')
            ->where('created_at', '>=', $oneHourAgo)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('business_metric_alerts')->insert([
            'metric_name'    => $metricName,
            'expected_value' => $expected,
            'actual_value'   => $actual,
            'deviation_pct'  => $deviationPct,
            'severity'       => $severity,
            'created_at'     => Carbon::now('UTC'),
        ]);
    }
}
