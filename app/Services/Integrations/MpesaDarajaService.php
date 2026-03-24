<?php

namespace App\Services\Integrations;

class MpesaDarajaService extends IntegrationService
{
    protected string $integrationName = 'mpesa_daraja';

    public function initiateSTKPush(string $phone, float $amount, string $reference): array
    {
        // TODO: implement M-Pesa STK push in Module 7 and Module 16
        throw new \RuntimeException('MpesaDarajaService::initiateSTKPush not yet implemented');
    }

    public function queryTransactionStatus(string $checkoutRequestId): array
    {
        // TODO: implement M-Pesa status query in Module 7
        throw new \RuntimeException('MpesaDarajaService::queryTransactionStatus not yet implemented');
    }

    public function verifyCallback(array $payload): bool
    {
        // TODO: implement Safaricom callback verification in Module 7
        throw new \RuntimeException('MpesaDarajaService::verifyCallback not yet implemented');
    }
}
