<?php

namespace App\Services\Integrations;

use App\Contracts\CourierProviderInterface;
use App\DTOs\DeliveryStatus;
use App\DTOs\DispatchNotification;

class SgaLogisticsService extends IntegrationService implements CourierProviderInterface
{
    protected string $integrationName = 'sga_logistics';

    public function notifyDispatch(DispatchNotification $notification): bool
    {
        // TODO: implement SGA dispatch API call in Module 4
        throw new \RuntimeException('SgaLogisticsService::notifyDispatch not yet implemented');
    }

    public function getDeliveryStatus(string $reference): DeliveryStatus
    {
        // TODO: implement SGA status query in Module 4
        throw new \RuntimeException('SgaLogisticsService::getDeliveryStatus not yet implemented');
    }

    public function confirmDelivery(string $reference): bool
    {
        // TODO: implement SGA delivery confirmation in Module 4
        throw new \RuntimeException('SgaLogisticsService::confirmDelivery not yet implemented');
    }
}
