<?php

return [
    'public_key' => env('LIQPAY_PUBLIC_KEY'),
    'private_key' => env('LIQPAY_PRIVATE_KEY'),
    'result_url' => env('LIQPAY_RESULT_URL'),
    'server_url' => env('LIQPAY_SERVER_URL', '/api/liqpay/webhook'),
];
