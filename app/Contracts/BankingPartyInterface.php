<?php

namespace App\Contracts;

use App\DTOs\Acknowledgement;
use App\DTOs\PaymentInstruction;
use App\DTOs\PaymentStatus;
use Illuminate\Http\Request;

interface BankingPartyInterface
{
    public function sendPaymentInstruction(PaymentInstruction $instruction): Acknowledgement;

    public function queryPaymentStatus(string $reference): PaymentStatus;

    public function verifyCallback(Request $request): bool;
}
