<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'),

    'base_url' => env('MPESA_ENV', 'sandbox') === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',

    'consumer_key'    => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    // STK Push (Lipa Na M-Pesa Online)
    'shortcode'    => env('MPESA_SHORTCODE', '174379'),
    'passkey'      => env('MPESA_PASSKEY'),
    'callback_url' => env('MPESA_STK_CALLBACK_URL'),
    'timeout_url'  => env('MPESA_TIMEOUT_URL'),

    // B2C (Bulk payout to pharmacy accounts)
    'b2c' => [
        'shortcode'           => env('MPESA_B2C_SHORTCODE', '600996'),
        'initiator_name'      => env('MPESA_B2C_INITIATOR_NAME', 'testapi'),
        'security_credential' => env('MPESA_B2C_SECURITY_CREDENTIAL'),
        'result_url'          => env('MPESA_B2C_RESULT_URL'),
        'timeout_url'         => env('MPESA_B2C_TIMEOUT_URL'),
    ],

    // Safaricom callback IP whitelist (production IPs)
    'callback_ips' => [
        '196.201.214.200',
        '196.201.214.206',
        '196.201.213.114',
        '196.201.214.207',
        '196.201.214.208',
        '196.201.213.44',
        '196.201.212.127',
        '196.201.212.138',
        '196.201.212.129',
        '196.201.212.136',
        '196.201.212.74',
        '196.201.212.69',
    ],

    // Access token cached for 55 min (tokens expire after 60 min)
    'token_cache_ttl' => 3300,
];
