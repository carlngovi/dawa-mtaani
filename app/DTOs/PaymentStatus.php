<?php

namespace App\DTOs;

class PaymentStatus
{
    public function __construct(
        public readonly string $reference,
        public readonly string $status,
        public readonly ?string $message = null,
    ) {}
}
