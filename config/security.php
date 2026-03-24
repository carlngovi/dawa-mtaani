<?php

return [

    'ip_whitelist' => [

        'mpesa_callbacks' => [
            '196.201.214.200',
            '196.201.214.206',
            '196.201.213.114',
            '196.201.214.207',
            '196.201.214.208',
            '175.41.238.173',
            '196.201.214.209',
            '196.201.214.210',
            '196.201.214.212',
            '196.201.214.210',
            '196.201.214.211',
            '196.201.214.213',
        ],

        'whatsapp_webhook' => [
            '31.13.24.0/21',
            '31.13.64.0/18',
            '45.64.40.0/22',
            '66.220.144.0/20',
            '69.63.176.0/20',
            '69.171.224.0/19',
            '74.119.76.0/22',
            '103.4.96.0/22',
            '129.134.0.0/17',
            '157.240.0.0/17',
            '173.252.64.0/19',
            '179.60.192.0/22',
            '185.60.216.0/22',
            '204.15.20.0/22',
        ],

    ],

    'session' => [
        'financial_timeout_minutes' => 15,
        'max_concurrent_sessions' => 3,
        'remember_me_days' => 30,
    ],

    'rate_limits' => [
        'public_per_minute' => 60,
        'authenticated_per_minute' => 300,
        'sensitive_per_hour' => 10,
    ],

    'mfa' => [
        'otp_expiry_minutes' => 10,
        'max_attempts_before_lock' => 3,
        'lock_duration_minutes' => 60,
        'backup_codes_count' => 10,
        'backup_codes_warn_threshold' => 3,
    ],

];
