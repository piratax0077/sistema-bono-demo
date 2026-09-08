<?php

return [
    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    'allow_demo' => filter_var(env('PAYMENT_ALLOW_DEMO', false), FILTER_VALIDATE_BOOLEAN),
];
