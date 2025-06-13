<?php

namespace Alyakin\LiqpayLaravel\DTO;

use Alyakin\LiqpayLaravel\Support\BaseDto;

/**
 * DTO для создания или обновления подписки в Liqpay
 *
 * @property string $order_id Идентификатор заказа
 * @property float $amount Сумма платежа
 * @property string $currency Валюта платежа
 * @property string $description Описание платежа
 * @property string $card Номер карты отправителя
 * @property string $card_cvv CVV-код карты
 * @property string $card_exp_month Месяц окончания действия карты (MM)
 * @property string $card_exp_year Год окончания действия карты (YY)
 * @property string $ip IP-адрес отправителя
 * @property string $phone Телефон отправителя
 * @property string|null $subscribe_date_start Дата начала подписки (UTC, формат "2015-03-31 00:00:00")
 * @property string|null $subscribe_periodicity Периодичность подписки: day|week|month|year
 * @property string|null $language Язык платежа (например, "ru" или "en")
 * @property string|null $prepare Признак подготовки платежа (например, "Y")
 * @property string|null $recurringbytoken Признак использования токена карты для подписки (например, "1")
 * @property string|null $recurring Признак подписки (например, "1")
 * @property string|null $server_url URL для получения уведомлений о статусе подписки
 * @property string|null $sender_address Адрес отправителя
 * @property string|null $sender_city Город отправителя
 * @property string|null $sender_country_code Цифровой код страны отправителя (ISO 3166-1)
 * @property string|null $sender_first_name Имя отправителя
 * @property string|null $sender_last_name Фамилия отправителя
 * @property string|null $sender_postal_code Почтовый индекс отправителя
 * @property string|null $customer Уникальный идентификатор клиента в магазине
 * @property string|null $dae Неизвестное поле dae (если нужно уточнить — желательно из доки)
 * @property string|null $info Дополнительные данные, возвращаемые в webhook
 * @property string|null $product_category Категория товара
 * @property string|null $product_description Описание товара
 * @property string|null $product_name Название товара
 * @property string|null $product_url URL страницы товара
 */
class LiqpaySubscriptionDto extends BaseDto
{
    public function __construct(
        public string $order_id,
        public float $amount,
        public string $currency,
        public string $description,
        public string $card,
        public string $card_cvv,
        public string $card_exp_month,
        public string $card_exp_year,
        public string $ip,
        public string $phone,
        public ?string $subscribe_date_start = null,
        public ?string $subscribe_periodicity = null,
        public ?string $language = null,
        public ?string $prepare = null,
        public ?string $recurringbytoken = null,
        public ?string $recurring = null,
        public ?string $server_url = null,
        public ?string $sender_address = null,
        public ?string $sender_city = null,
        public ?string $sender_country_code = null,
        public ?string $sender_first_name = null,
        public ?string $sender_last_name = null,
        public ?string $sender_postal_code = null,
        public ?string $customer = null,
        public ?string $dae = null,
        public ?string $info = null,
        public ?string $product_category = null,
        public ?string $product_description = null,
        public ?string $product_name = null,
        public ?string $product_url = null
    ) {}
}
