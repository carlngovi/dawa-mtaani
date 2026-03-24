<?php

namespace App\Contracts;

interface SmsGatewayInterface
{
    public function sendSms(string $phone, string $message): bool;

    public function initiateUssdSession(string $phone, string $menuText): string;
}
