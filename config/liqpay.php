<?php

return [
    'public_key' => env('LIQPAY_PUBLIC_KEY'),
    'private_key' => env('LIQPAY_PRIVATE_KEY'),
    'result_url' => env('LIQPAY_RESULT_URL'),
    'server_url' => env('LIQPAY_SERVER_URL', '/api/liqpay/webhook'),

    'archive_from' => env('LIQPAY_ARCHIVE_FROM', now()->subMonth()->toDateString()),
    'archive_to' => env('LIQPAY_ARCHIVE_TO', now()->toDateString()),
    'cache_ttl' => env('LIQPAY_CACHE_TTL', 60 * 60 * 24), // 1 day in seconds
];
