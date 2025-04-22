<?php

namespace Alyakin\LiqPayLaravel\DTO;

use Alyakin\LiqPayLaravel\Support\BaseDto;

/**
 * Class LiqPayRequestDto
 *
 * DTO для формирования ссылки на оплату через LiqPay.
 *
 * Полный список параметров согласно официальной документации:
 * https://www.liqpay.ua/documentation/api/aquiring/checkout/doc
 *
 * @property string $version Версия API. Всегда "3".
 * @property string $public_key Публичный ключ мерчанта.
 * @property string $action Тип действия: pay | subscribe.
 * @property float $amount Сумма платежа.
 * @property string $currency Валюта: UAH, USD, EUR и т.д.
 * @property string $order_id Уникальный идентификатор заказа.
 * @property string $description Описание заказа.
 * @property string|null $language Язык страницы оплаты (uk, en, ru).
 * @property string|null $result_url URL для редиректа после оплаты.
 * @property string|null $server_url URL для получения webhook.
 * @property string|null $info Пользовательские данные, возвращаются в callback.
 * @property int|null $subscribe Подписка: 1 = включена.
 * @property string|null $subscribe_date_start Дата начала подписки (YYYY-MM-DD).
 * @property string|null $subscribe_periodicity Период подписки: day|month|year.
 * @property string|null $sender_email Email плательщика.
 * @property string|null $sender_phone Телефон плательщика.
 * @property string|null $sender_first_name Имя плательщика.
 * @property string|null $sender_last_name Фамилия плательщика.
 * @property string|null $ip IP-адрес плательщика.
 * @property string|null $lang Язык интерфейса (дублирует language).
 * @property string|null $product_category Категория товара.
 * @property string|null $product_description Описание товара.
 * @property string|null $encoding Кодировка, по умолчанию UTF-8.
 * @property string|null $recurring_currency Валюта регулярного списания.
 * @property string|null $customer Идентификатор клиента.
 * @property string|null $subscribe_id ID подписки для повторных платежей.
 * @property string|null $card_type Тип карты (visa, mastercard).
 * @property string|null $device_type Тип устройства.
 */
class LiqPayRequestDto extends BaseDto
{
    public function __construct(
        public string $version,
        public string $public_key,
        public string $action,
        public float $amount,
        public string $currency,
        public string $order_id,
        public string $description,
        public ?string $language = null,
        public ?string $result_url = null,
        public ?string $server_url = null,
        public ?string $info = null,
        public ?int $subscribe = null,
        public ?string $subscribe_date_start = null,
        public ?string $subscribe_periodicity = null,
        public ?string $sender_email = null,
        public ?string $sender_phone = null,
        public ?string $sender_first_name = null,
        public ?string $sender_last_name = null,
        public ?string $ip = null,
        public ?string $lang = null,
        public ?string $product_category = null,
        public ?string $product_description = null,
        public ?string $encoding = null,
        public ?string $recurring_currency = null,
        public ?string $customer = null,
        public ?string $subscribe_id = null,
        public ?string $card_type = null,
        public ?string $device_type = null,
    ) {}
}
