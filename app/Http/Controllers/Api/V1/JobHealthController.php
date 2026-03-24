<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobHealthController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $jobNames = DB::table('job_monitoring')
            ->distinct()
            ->pluck('job_name');

        $health = [];

        foreach ($jobNames as $jobName) {
            $lastRun = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->orderBy('started_at', 'desc')
                ->first();

            $avgDuration = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->where('status', 'COMPLETED')
                ->where('started_at', '>=', Carbon::now('UTC')->subDays(7))
                ->avg('duration_ms');

            $lastThree = DB::table('job_monitoring')
                ->where('job_name', $jobName)
                ->orderBy('started_at', 'desc')
                ->limit(3)
                ->pluck('status')
                ->toArray();

            $consecutiveFailures = 0;
            foreach ($lastThree as $status) {
                if ($status === 'FAILED') {
                    $consecutiveFailures++;
                } else {
                    break;
                }
            }

            $shortName = class_basename($jobName);

            $health[] = [
                'job_name'             => $shortName,
                'full_class'           => $jobName,
                'last_run_at'          => $lastRun?->started_at,
                'last_status'          => $lastRun?->status,
                'last_duration_ms'     => $lastRun?->duration_ms,
                'avg_duration_ms_7d'   => $avgDuration ? round($avgDuration) : null,
                'consecutive_failures' => $consecutiveFailures,
                'has_active_alert'     => $consecutiveFailures >= 3,
            ];
        }

        return response()->json([
            'jobs'         => $health,
            'generated_at' => Carbon::now('UTC')->toISOString(),
        ]);
    }
}
