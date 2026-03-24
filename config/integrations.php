<?php

return [

    'sms' => [
        'primary' => [
            'driver' => 'africas_talking',
            'service' => \App\Services\Integrations\AfricasTalkingService::class,
        ],
        'fallback' => [
            'driver' => 'twilio',
            'service' => null, // TODO: implement TwilioService in Module 27
        ],
        'max_failures_before_fallback' => 3,
    ],

    'payments' => [
        'primary' => [
            'driver' => 'mpesa',
            'service' => \App\Services\Integrations\MpesaDarajaService::class,
        ],
        'fallback' => [
            'driver' => 'airtel_money',
            'service' => null, // TODO: implement AirtelMoneyService in Module 27
        ],
        'max_failures_before_fallback' => 3,
    ],

];
