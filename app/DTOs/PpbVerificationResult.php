<?php

namespace App\DTOs;

class PpbVerificationResult
{
    public function __construct(
        public readonly bool $found,
        public readonly ?string $facilityName = null,
        public readonly ?string $ppbType = null,
        public readonly ?string $licenceStatus = null,
        public readonly ?string $registeredAddress = null,
        public readonly ?string $message = null,
    ) {}
}
