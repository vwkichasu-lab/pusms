<?php

return [
    'email' => [
        'provider' => env('PUSMS_EMAIL_PROVIDER', 'smtp'),
        'timeout' => (int) env('PUSMS_EMAIL_TIMEOUT', 30),
    ],

    'sms' => [
        'provider' => env('PUSMS_SMS_PROVIDER', 'hubtel'),
        'default_country_code' => env('PUSMS_SMS_DEFAULT_COUNTRY_CODE'),
        'max_length' => (int) env('PUSMS_SMS_MAX_LENGTH', 918),
        'timeout' => (int) env('PUSMS_SMS_TIMEOUT', 30),
    ],
];
