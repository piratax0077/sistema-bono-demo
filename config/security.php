<?php

return [
    'review_demo_auth_bypass' => filter_var(
        env('REVIEW_DEMO_AUTH_BYPASS', env('APP_ENV', 'production') === 'local'),
        FILTER_VALIDATE_BOOLEAN
    ),
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://127.0.0.1:4173,http://localhost:4173'))
    ))),

    'totem_origin' => env('TOTEM_ORIGIN', 'http://127.0.0.1:4173'),

    'totem_session_minutes' => (int) env('TOTEM_SESSION_MINUTES', 30),

    'require_totem_ip' => filter_var(
        env('TOTEM_REQUIRE_IP', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    'device_enrollment_key' => env('DEVICE_ENROLLMENT_KEY'),

    'public_registration' => filter_var(
        env('ALLOW_PUBLIC_REGISTRATION', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    'ip_whitelist_enabled' => filter_var(
        env('IP_WHITELIST_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),
];
