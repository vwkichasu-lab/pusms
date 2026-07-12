<?php

return [
    'maintenance_token' => env('PUSMS_MAINTENANCE_TOKEN'),

    'email' => [
        'provider' => env('PUSMS_EMAIL_PROVIDER', 'smtp'),
        'timeout' => (int) env('PUSMS_EMAIL_TIMEOUT', 30),
    ],
];
