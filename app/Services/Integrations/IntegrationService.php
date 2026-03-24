<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class IntegrationService
{
    protected string $integrationName = 'unknown';

    protected function callWithRetry(callable $fn): mixed
    {
        $attempts = 0;
        $backoff = [30, 60, 120];

        while ($attempts < 3) {
            try {
                $start = microtime(true);
                $result = $fn();
                $duration = (int) ((microtime(true) - $start) * 1000);

                $this->log('OUTBOUND', 'success', null, $duration);

                return $result;
            } catch (Throwable $e) {
                $attempts++;
                $this->log('OUTBOUND', 'failed', $e->getMessage(), 0);

                if ($attempts >= 3) {
                    throw $e;
                }

                sleep($backoff[$attempts - 1]);
            }
        }

        throw new \RuntimeException("callWithRetry exhausted all attempts for {$this->integrationName}");
    }

    protected function log(
        string $direction,
        string $status,
        ?string $error,
        int $durationMs,
        string $endpoint = '',
        ?array $request = null,
        ?array $response = null,
    ): void {
        try {
            DB::table('integration_logs')->insert([
                'integration_name' => $this->integrationName,
                'direction' => $direction,
                'endpoint' => $endpoint,
                'request_payload' => $request ? json_encode($request) : null,
                'response_payload' => $response ? json_encode($response) : null,
                'http_status' => null,
                'success' => $status === 'success',
                'error_message' => $error,
                'duration_ms' => $durationMs,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning("IntegrationService: failed to write log for {$this->integrationName}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
