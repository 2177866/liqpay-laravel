<?php

return [
    'public_key' => env('LIQPAY_PUBLIC_KEY'),
    'private_key' => env('LIQPAY_PRIVATE_KEY'),
    'sandbox' => env('LIQPAY_SANDBOX', true),
    'result_url' => env('LIQPAY_RESULT_URL'),
    'server_url' => env('LIQPAY_SERVER_URL', '/api/liqpay/webhook'),
];
