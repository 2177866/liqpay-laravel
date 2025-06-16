<?php

return [
    'archive_processed' => 'Архив успешно обработан, кеш и временный файл очищены.',
    'archive_downloaded' => 'Архив скачан и закеширован: :file',
    'archive_continue' => 'Продолжаем с файла: :file, строка: :line',
    'download_failed' => 'Ошибка скачивания архива или архив пуст.',
    'file_open_failed' => 'Не удалось открыть файл: :file',
    'malformed_archive' => 'Неправильный формат архива, ожидается JSON.',
    'error_at_line' => 'Ошибка на строке :line: :msg',
    'processed_payments' => 'Обработано: :count записей.',
    'json_decode_error' => 'Ошибка декодирования JSON: :error',
    'payment_system_error' => 'Ошибка платежной системы[:code]: :description',
    'liqpay_api_error' => 'Ошибка API Liqpay: :request, :response',

    // Исключения
    'config_missing' => 'Конфигурация Liqpay не задана.',
    'invalid_url' => 'Некорректный URL для Liqpay checkout.',
    'json_encode_failed' => 'JSON кодирование не удалось: :error',
    'invalid_signature' => 'Недействительная подпись Liqpay.',

    'invalid_json_response' => 'Недействительный JSON ответ от Liqpay.',
];
