<?php

namespace App\Services;

use App\Contracts\PpbRegistryInterface;
use App\DTOs\PpbVerificationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpbVerificationService
{
    public function __construct(
        private readonly PpbRegistryInterface $registry
    ) {}

    public function verify(string $licenceNumber): PpbVerificationResult
    {
        try {
            $result = $this->registry->verifyLicence($licenceNumber);

            // Log the verification attempt
            $this->logVerification($licenceNumber, $result);

            return $result;

        } catch (\Throwable $e) {
            Log::error('PpbVerificationService: verification failed', [
                'licence_number' => $licenceNumber,
                'error'          => $e->getMessage(),
            ]);

            return new PpbVerificationResult(
                found: false,
                message: 'PPB verification service unavailable. Please try again later.'
            );
        }
    }

    public function isRegistryStale(): bool
    {
        $staleDays = (int) (DB::table('system_settings')
            ->where('key', 'ppb_registry_stale_days')
            ->value('value') ?? 7);

        $lastUpload = DB::table('ppb_registry_cache')
            ->max('last_uploaded_at');

        if (! $lastUpload) {
            return true;
        }

        return now('UTC')->diffInDays($lastUpload) > $staleDays;
    }

    private function logVerification(string $licenceNumber, PpbVerificationResult $result): void
    {
        try {
            DB::table('integration_logs')->insert([
                'integration_name' => 'ppb_registry_file',
                'direction'        => 'OUTBOUND',
                'endpoint'         => 'verifyLicence',
                'request_payload'  => json_encode(['licence_number' => $licenceNumber]),
                'response_payload' => json_encode([
                    'found'           => $result->found,
                    'ppb_type'        => $result->ppbType,
                    'licence_status'  => $result->licenceStatus,
                ]),
                'success'          => $result->found,
                'duration_ms'      => 0,
                'created_at'       => now('UTC'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PpbVerificationService: failed to log verification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
