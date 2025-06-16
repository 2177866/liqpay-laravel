<?php

return [
    /**
     * LiqPay API keys and settings.
     */
    'public_key' => env('LIQPAY_PUBLIC_KEY'),
    'private_key' => env('LIQPAY_PRIVATE_KEY'),

    /**
     * LiqPay API URLs.
     * These URLs are used for checkout and webhook handling.
     */
    'result_url' => env('LIQPAY_RESULT_URL'),
    'server_url' => env('LIQPAY_SERVER_URL', '/api/liqpay/webhook'),

    /**
     * Archive importing settings (for Artisan command liqpay:sync-subscriptions).
     * These settings control the date range for the archive,
     * the cache TTL for the archive file,
     */
    'archive_from' => env('LIQPAY_ARCHIVE_FROM', now()->subDay(90)->toDateString()),
    'archive_to' => env('LIQPAY_ARCHIVE_TO', now()->toDateString()),
    'cache_ttl' => env('LIQPAY_CACHE_TTL', 60 * 60 * 24), // 1 day in seconds
];
