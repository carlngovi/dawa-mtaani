<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class IntegrationHealthController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $integrationNames = [
            'im_bank',
            'sga_logistics',
            'mpesa_daraja',
            'ppb_registry_file',
            'africas_talking',
        ];

        $since24h = now('UTC')->subHours(24);

        $integrations = [];

        foreach ($integrationNames as $name) {
            $lastCalled = DB::table('integration_logs')
                ->where('integration_name', $name)
                ->max('created_at');

            $lastSuccess = DB::table('integration_logs')
                ->where('integration_name', $name)
                ->where('success', true)
                ->max('created_at');

            $total24h = DB::table('integration_logs')
                ->where('integration_name', $name)
                ->where('created_at', '>=', $since24h)
                ->count();

            $failed24h = DB::table('integration_logs')
                ->where('integration_name', $name)
                ->where('created_at', '>=', $since24h)
                ->where('success', false)
                ->count();

            $successRate = $total24h > 0
                ? round((($total24h - $failed24h) / $total24h) * 100, 2)
                : null;

            $integrations[] = [
                'integration_name' => $name,
                'last_called_at' => $lastCalled,
                'last_success_at' => $lastSuccess,
                'success_rate_24h' => $successRate,
                'total_calls_24h' => $total24h,
                'failed_calls_24h' => $failed24h,
            ];
        }

        $queueNames = ['payments', 'notifications', 'sync', 'reports', 'quality-flags', 'default'];
        $queues = [];

        try {
            $prefix = config('database.redis.options.prefix', '');
            foreach ($queueNames as $queueName) {
                $queues[$queueName] = (int) Redis::llen($prefix . 'queues:' . $queueName);
            }
        } catch (\Throwable) {
            foreach ($queueNames as $queueName) {
                $queues[$queueName] = null;
            }
        }

        return response()->json([
            'integrations' => $integrations,
            'queues' => $queues,
            'generated_at' => now('UTC')->toISOString(),
        ]);
    }
}
