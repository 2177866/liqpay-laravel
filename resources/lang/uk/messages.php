<?php

return [
    'archive_processed' => 'Архів успішно оброблено, кеш і тимчасовий файл очищено.',
    'archive_downloaded' => 'Архів завантажено та кешовано: :file',
    'archive_continue' => 'Продовжуємо з файлу: :file, рядок: :line',
    'download_failed' => 'Помилка завантаження архіву або архів порожній.',
    'file_open_failed' => 'Не вдалося відкрити файл: :file',
    'malformed_archive' => 'Неправильний формат архіву, очікується JSON.',
    'error_at_line' => 'Помилка у рядку :line: :msg',
    'processed_payments' => 'Опрацьовано: :count записів.',
    'json_decode_error' => 'Помилка декодування JSON: :error',
    'payment_system_error' => 'Помилка платіжної системи[:code]: :description',
    'liqpay_api_error' => 'Помилка API Liqpay: :request, :response',

    // Исключения
    'config_missing' => 'Конфігурація Liqpay не задана.',
    'invalid_url' => 'Некоректний URL для Liqpay checkout.',
    'json_encode_failed' => 'JSON кодування не вдалося: :error',
    'invalid_signature' => 'Недійсна підпис Liqpay.',

    'invalid_json_response' => 'Недійсний JSON відповідь від Liqpay.',

];
