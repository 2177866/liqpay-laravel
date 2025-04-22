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
 * @property string $action Тип действия: pay | subscribe | hold | paydonate.
 * @property float $amount Сумма платежа.
 * @property string $currency Валюта: UAH, USD, EUR и т.д.
 * @property string $description Описание заказа.
 * @property string $order_id Уникальный идентификатор заказа.
 * @property array{items: array<array{amount: int, cost: int, id: int, price: int}>, delivery_emails: array<string>}|null $rro_info Информация для РРО: массив items и delivery_emails
 * @property string|null $expired_date Дата окончания действия ссылки, формат "2016-04-24 00:00:00"
 * @property string|null $language Язык страницы оплаты (uk, en).
 * @property string|null $paytypes Методы оплаты (apay, gpay, card, privat24, qr и т.д.)
 * @property string|null $result_url URL для редиректа после оплаты.
 * @property string|null $server_url URL для получения webhook.
 * @property string|null $verifycode Признак верификации (например, 'Y')
 * @property string|null $sender_address Адрес отправителя.
 * @property string|null $sender_city Город отправителя.
 * @property string|null $sender_country_code Цифровой код страны отправителя (ISO 3166-1)
 * @property string|null $sender_first_name Имя плательщика.
 * @property string|null $sender_last_name Фамилия плательщика.
 * @property string|null $sender_postal_code Почтовый индекс.
 * @property int|null $subscribe Признак подписки (1 = включена).
 * @property string|null $subscribe_date_start Дата первого платежа (UTC, формат "2015-03-31 00:00:00")
 * @property string|null $subscribe_periodicity Период подписки: day|week|month|year.
 * @property string|null $customer Идентификатор клиента.
 * @property string|null $recurringbytoken Признак генерации card_token ("1")
 * @property string|null $customer_user_id ID пользователя клиента.
 * @property string|null $dae Неизвестное поле dae (если нужно уточнить — желательно из доки).
 * @property string|null $info Дополнительные данные, возвращаются в webhook.
 * @property string|null $product_category Категория товара.
 * @property string|null $product_description Описание товара.
 * @property string|null $product_name Название товара.
 * @property string|null $product_url URL страницы товара.
 */
class LiqPayRequestDto extends BaseDto
{
    public function __construct(
        public string $version,
        public string $public_key,
        public string $action,
        public float $amount,
        public string $currency,
        public string $description,
        public string $order_id,

        /** @var array{
         *   items: array<array{amount: int, cost: int, id: int, price: int}>,
         *   delivery_emails: array<string>
         * }|null $rro_info */
        public ?array $rro_info = null,

        public ?string $expired_date = null,
        public ?string $language = null,
        public ?string $paytypes = null,
        public ?string $result_url = null,
        public ?string $server_url = null,
        public ?string $verifycode = null,

        public ?string $sender_address = null,
        public ?string $sender_city = null,
        public ?string $sender_country_code = null,
        public ?string $sender_first_name = null,
        public ?string $sender_last_name = null,
        public ?string $sender_postal_code = null,

        public ?int $subscribe = null,
        public ?string $subscribe_date_start = null,
        public ?string $subscribe_periodicity = null,

        public ?string $customer = null,
        public ?string $recurringbytoken = null,
        public ?string $customer_user_id = null,
        public ?string $dae = null,

        public ?string $info = null,
        public ?string $product_category = null,
        public ?string $product_description = null,
        public ?string $product_name = null,
        public ?string $product_url = null,
    ) {}
}
