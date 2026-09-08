<?php

return [
    'base_url' => env('PERSONAS_API_URL', 'http://127.0.0.1:8020'),
    'token' => env('PERSONAS_API_TOKEN'),
    'timeout' => (int) env('PERSONAS_API_TIMEOUT', 3),
    'fallback_local' => (bool) env('PERSONAS_API_FALLBACK_LOCAL', true),
];
