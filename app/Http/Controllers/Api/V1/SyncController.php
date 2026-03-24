<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class SyncController extends Controller
{
    public function push(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $maxPerMinute = (int) (DB::table('system_settings')
            ->where('key', 'sync_rate_limit_per_minute')
            ->value('value') ?? 1000);

        $rateLimitKey = 'sync:facility:' . $facilityId;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxPerMinute)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'message' => 'Too many sync items. Please slow down.',
                'retry_after_seconds' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => 60,
            ]);
        }

        $request->validate([
            'items' => 'required|array',
        ]);

        $items = $request->input('items');
        $results = [];

        foreach ($items as $item) {
            try {
                $idempotencyKey = $item['idempotency_key'] ?? null;

                if ($idempotencyKey) {
                    $existing = DB::table('sync_queue_items')
                        ->where('idempotency_key', $idempotencyKey)
                        ->first();

                    if ($existing) {
                        $results[] = [
                            'idempotency_key' => $idempotencyKey,
                            'status' => 'already_processed',
                            'original_status' => $existing->status,
                        ];
                        continue;
                    }
                }

                DB::table('sync_queue_items')->insert([
                    'facility_id' => $facilityId,
                    'idempotency_key' => $idempotencyKey,
                    'device_id' => $request->header('X-Device-ID', 'unknown'),
                    'payload' => json_encode($item),
                    'status' => 'PENDING',
                    'device_created_at' => $item['device_created_at'] ?? null,
                    'received_at' => now('UTC'),
                    'created_at' => now('UTC'),
                    'updated_at' => now('UTC'),
                ]);

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'accepted',
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'idempotency_key' => $item['idempotency_key'] ?? null,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        RateLimiter::hit($rateLimitKey, 60);

        return response()->json([
            'processed' => count($results),
            'results' => $results,
        ]);
    }

    public function pull(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $lastSyncAt = $request->query('last_sync_at')
            ? Carbon::parse($request->query('last_sync_at'), 'UTC')
            : now('UTC')->subHours(24);

        $products = DB::table('products')
            ->where('is_active', true)
            ->where('updated_at', '>=', $lastSyncAt)
            ->get();

        $orders = DB::table('orders')
            ->where('facility_id', $facilityId)
            ->where('updated_at', '>=', $lastSyncAt)
            ->select(['id', 'ulid', 'status', 'updated_at'])
            ->get();

        $creditSnapshot = [];
        try {
            $creditSnapshot = DB::table('facility_tranche_balances')
                ->where('facility_id', $facilityId)
                ->get();
        } catch (Throwable) {
            // Table may not exist yet — return empty array
        }

        return response()->json([
            'synced_at' => now('UTC')->toISOString(),
            'data' => [
                'products' => $products,
                'orders' => $orders,
                'credit_snapshot' => $creditSnapshot,
            ],
        ]);
    }

    public function smsIncoming(Request $request): JsonResponse
    {
        $text = trim($request->input('text', ''));
        $from = $request->input('from', '');

        $parts = preg_split('/\s+/', $text, 2);
        $command = strtoupper($parts[0] ?? '');

        $reply = match (true) {
            str_starts_with($command, 'BAL') => 'Credit balance query received. Feature active in Phase 2.',
            str_starts_with($command, 'CONFIRM') => 'Delivery confirmation received.',
            str_starts_with($command, 'FLAG') => 'Quality flag received. Thank you.',
            default => 'Unknown command. Send BAL, CONFIRM or FLAG.',
        };

        try {
            DB::table('integration_logs')->insert([
                'integration_name' => 'africas_talking',
                'direction' => 'INBOUND',
                'endpoint' => '/api/v1/sms/incoming',
                'request_payload' => json_encode(['from' => $from, 'text' => $text]),
                'response_payload' => json_encode(['reply' => $reply]),
                'http_status' => 200,
                'success' => true,
                'error_message' => null,
                'duration_ms' => 0,
                'created_at' => now('UTC'),
            ]);
        } catch (Throwable $e) {
            Log::warning('SyncController: failed to log incoming SMS', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'processed']);
    }
}
