<?php

namespace App\Contracts;

use App\DTOs\PpbVerificationResult;

interface PpbRegistryInterface
{
    public function verifyLicence(string $licenceNumber): PpbVerificationResult;
}
