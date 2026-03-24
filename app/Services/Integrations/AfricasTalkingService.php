<?php

namespace App\Services\Integrations;

use App\Contracts\SmsGatewayInterface;

class AfricasTalkingService extends IntegrationService implements SmsGatewayInterface
{
    protected string $integrationName = 'africas_talking';

    public function sendSms(string $phone, string $message): bool
    {
        // TODO: implement Africa's Talking SMS in Module 18 and 23
        throw new \RuntimeException('AfricasTalkingService::sendSms not yet implemented');
    }

    public function initiateUssdSession(string $phone, string $menuText): string
    {
        // TODO: implement USSD session in Module 18
        throw new \RuntimeException('AfricasTalkingService::initiateUssdSession not yet implemented');
    }
}
