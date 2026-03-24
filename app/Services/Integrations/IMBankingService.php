<?php

namespace App\Services\Integrations;

use App\Contracts\BankingPartyInterface;
use App\DTOs\Acknowledgement;
use App\DTOs\PaymentInstruction;
use App\DTOs\PaymentStatus;
use Illuminate\Http\Request;

class IMBankingService extends IntegrationService implements BankingPartyInterface
{
    protected string $integrationName = 'im_bank';

    public function sendPaymentInstruction(PaymentInstruction $instruction): Acknowledgement
    {
        // TODO: implement I&M Bank API call in Module 7
        throw new \RuntimeException('IMBankingService::sendPaymentInstruction not yet implemented');
    }

    public function queryPaymentStatus(string $reference): PaymentStatus
    {
        // TODO: implement I&M Bank status query in Module 7
        throw new \RuntimeException('IMBankingService::queryPaymentStatus not yet implemented');
    }

    public function verifyCallback(Request $request): bool
    {
        // TODO: implement HMAC verification in Module 7
        throw new \RuntimeException('IMBankingService::verifyCallback not yet implemented');
    }
}
