<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', 'stripe'),

    'currency' => env('CHECKOUT_CURRENCY', 'usd'),

    'default_price_cents' => (int) env('CHECKOUT_DEFAULT_PRICE_CENTS', 500),

    'download_expiry_hours' => (int) env('CHECKOUT_DOWNLOAD_EXPIRY_HOURS', 48),

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
];
