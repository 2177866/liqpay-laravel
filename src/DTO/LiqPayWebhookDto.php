<?php

namespace Alyakin\LiqPayLaravel\DTO;

use Alyakin\LiqPayLaravel\Support\BaseDto;

/**
 * Class LiqPayWebhookDto
 *
 * DTO для данных, полученных от LiqPay в webhook (callback).
 *
 * @property int|null $version Версия API. Текущее значение — 3.
 * @property string|null $public_key Публичный ключ магазина.
 * @property string|null $action Тип транзакции: pay — платеж, hold — блокировка, subscribe — подписка и т.д.
 * @property string|null $status Статус платежа: success, failure, 3ds_verify, invoice_wait и др.
 * @property float|null $amount Сумма платежа.
 * @property string|null $currency Валюта платежа.
 * @property string|null $order_id Идентификатор заказа.
 * @property string|null $liqpay_order_id Внутренний order_id в системе LiqPay.
 * @property string|null $description Описание платежа.
 * @property string|null $create_date Дата создания платежа.
 * @property string|null $completion_date Дата списания средств.
 * @property int|null $payment_id Уникальный ID платежа в системе LiqPay.
 * @property string|null $paytype Метод оплаты: card, privat24, qr и т.д.
 * @property string|null $err_code Код ошибки, если есть.
 * @property string|null $err_erc Код ошибки ERC.
 * @property string|null $err_description Описание ошибки.
 * @property string|null $info Дополнительная информация по платежу.
 * @property string|null $ip IP-адрес отправителя.
 * @property bool|null $is_3ds Использована ли 3DS проверка.
 * @property string|null $redirect_to Ссылка, на которую нужно перенаправить пользователя для подтверждения.
 * @property float|null $amount_debit Сумма списания в валюте списания.
 * @property float|null $amount_credit Сумма зачисления в валюте зачисления.
 * @property float|null $amount_bonus Сумма бонуса отправителя.
 * @property float|null $sender_bonus Бонус отправителя в валюте платежа.
 * @property float|null $sender_commission Комиссия отправителя в валюте платежа.
 * @property float|null $receiver_commission Комиссия получателя в валюте платежа.
 * @property float|null $agent_commission Комиссия агента.
 * @property float|null $commission_debit Комиссия отправителя в валюте списания.
 * @property float|null $commission_credit Комиссия получателя в валюте зачисления.
 * @property string|null $authcode_credit Код авторизации для зачисления.
 * @property string|null $authcode_debit Код авторизации для списания.
 * @property string|null $rrn_credit RRN код зачисления.
 * @property string|null $rrn_debit RRN код списания.
 * @property string|null $card_token Токен карты отправителя.
 * @property string|null $token Токен платежа.
 * @property string|null $type Тип платежа.
 * @property string|null $currency_debit Валюта списания.
 * @property string|null $currency_credit Валюта зачисления.
 * @property string|null $customer Уникальный идентификатор клиента в магазине.
 * @property int|null $acq_id ID эквайера.
 * @property int|bool|null $wait_reserve_status Отметка, что платёж зарезервирован под возврат.
 * @property string|null $sender_card_mask2 Маска карты отправителя.
 * @property string|null $sender_card_type Тип карты отправителя (MC/Visa).
 * @property string|null $sender_card_bank Банк карты отправителя.
 * @property string|null $sender_card_country Цифровой код страны карты отправителя (ISO 3166-1).
 * @property string|null $sender_email Email отправителя.
 * @property string|null $sender_phone Телефон отправителя.
 * @property string|null $sender_first_name Имя отправителя.
 * @property string|null $sender_last_name Фамилия отправителя.
 * @property string|null $mpi_eci Код 3DS-авторизации: 5, 6, 7.
 * @property string|null $product_category Категория продукта в магазине.
 * @property string|null $product_description Описание продукта.
 * @property string|null $product_name Название продукта.
 * @property string|null $product_url URL страницы продукта.
 * @property float|null $refund_amount Сумма возврата.
 * @property string|null $refund_date_last Последняя дата возврата.
 * @property string|null $verifycode Код верификации.
 */
class LiqPayWebhookDto extends BaseDto
{
    public function __construct(
        public ?int $version = null,
        public ?string $public_key = null,
        public ?string $action = null,
        public ?string $status = null,
        public ?float $amount = null,
        public ?string $currency = null,
        public ?string $order_id = null,
        public ?string $liqpay_order_id = null,
        public ?string $description = null,
        public ?string $create_date = null,
        public ?string $completion_date = null,
        public ?int $payment_id = null,
        public ?string $paytype = null,
        public ?string $err_code = null,
        public ?string $err_erc = null,
        public ?string $err_description = null,
        public ?string $info = null,
        public ?string $ip = null,
        public ?bool $is_3ds = null,
        public ?string $redirect_to = null,
        public ?float $amount_debit = null,
        public ?float $amount_credit = null,
        public ?float $amount_bonus = null,
        public ?float $sender_bonus = null,
        public ?float $sender_commission = null,
        public ?float $receiver_commission = null,
        public ?float $agent_commission = null,
        public ?float $commission_debit = null,
        public ?float $commission_credit = null,
        public ?string $authcode_credit = null,
        public ?string $authcode_debit = null,
        public ?string $rrn_credit = null,
        public ?string $rrn_debit = null,
        public ?string $card_token = null,
        public ?string $token = null,
        public ?string $type = null,
        public ?string $currency_debit = null,
        public ?string $currency_credit = null,
        public ?string $customer = null,
        public ?int $acq_id = null,
        public int|bool|null $wait_reserve_status = null,
        public ?string $sender_card_mask2 = null,
        public ?string $sender_card_type = null,
        public ?string $sender_card_bank = null,
        public ?string $sender_card_country = null,
        public ?string $sender_email = null,
        public ?string $sender_phone = null,
        public ?string $sender_first_name = null,
        public ?string $sender_last_name = null,
        public ?string $mpi_eci = null,
        public ?string $product_category = null,
        public ?string $product_description = null,
        public ?string $product_name = null,
        public ?string $product_url = null,
        public ?float $refund_amount = null,
        public ?string $refund_date_last = null,
        public ?string $verifycode = null,
        public ?int $end_date = null,

        // undocumented
        public ?string $language = null,
        public ?int $transaction_id = null,
    ) {}
}
