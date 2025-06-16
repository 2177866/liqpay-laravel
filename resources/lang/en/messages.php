<?php

return [
    'archive_processed' => 'Archive processed successfully, cache and temp file cleared.',
    'archive_downloaded' => 'Archive downloaded and cached: :file',
    'archive_continue' => 'Continue from file: :file, line: :line',
    'download_failed' => 'Archive download failed or empty.',
    'file_open_failed' => 'Unable to open file: :file',
    'malformed_archive' => 'Malformed archive, expected JSON format.',
    'error_at_line' => 'Error at line :line: :msg',
    'processed_payments' => 'Processed :count recorde.',
    'json_decode_error' => 'JSON decode error: :error',
    'payment_system_error' => 'Payment system error[:code]: :description',
    'liqpay_api_error' => 'Liqpay API error: :request, :response',

    // Исключения
    'config_missing' => 'Liqpay configuration is not set.',
    'invalid_url' => 'Invalid Liqpay checkout URL.',
    'json_encode_failed' => 'JSON encode failed with error: :error',
    'invalid_signature' => 'Invalid Liqpay signature.',

    'invalid_json_response' => 'Invalid JSON response from Liqpay.',
];
