<?php

namespace App\Contracts;

use App\DTOs\DeliveryStatus;
use App\DTOs\DispatchNotification;

interface CourierProviderInterface
{
    public function notifyDispatch(DispatchNotification $notification): bool;

    public function getDeliveryStatus(string $reference): DeliveryStatus;

    public function confirmDelivery(string $reference): bool;
}
