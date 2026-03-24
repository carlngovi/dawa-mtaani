<?php

namespace App\DTOs;

class DispatchNotification
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $orderReference,
        public readonly string $facilityName,
        public readonly string $deliveryAddress,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $courierReference = null,
    ) {}
}
