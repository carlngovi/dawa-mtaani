<?php

namespace App\Services\Integrations;

use App\Contracts\PpbRegistryInterface;
use App\DTOs\PpbVerificationResult;
use Illuminate\Support\Facades\DB;

class PpbRegistryFileService extends IntegrationService implements PpbRegistryInterface
{
    protected string $integrationName = 'ppb_registry_file';

    public function verifyLicence(string $licenceNumber): PpbVerificationResult
    {
        $record = DB::table('ppb_registry_cache')
            ->where('licence_number', $licenceNumber)
            ->first();

        if (! $record) {
            return new PpbVerificationResult(
                found: false,
                message: 'Licence not in current PPB registry. Upload a fresh registry file or verify manually.'
            );
        }

        return new PpbVerificationResult(
            found: true,
            facilityName: $record->facility_name,
            ppbType: $record->ppb_type,
            licenceStatus: $record->licence_status,
            registeredAddress: $record->registered_address,
        );
    }
}
