<?php

namespace App\DTOs;

class PaymentInstruction
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $partyId,
        public readonly float $amount,
        public readonly string $partyAccount,
        public readonly string $orderReference,
        public readonly string $trancheReference,
        public readonly string $facilityIdentifier,
        public readonly string $idempotencyKey,
    ) {}
}
