<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayWebhookReceived
 * Событие при любом успешном получении webhook от Liqpay.
 */
class LiqpayWebhookReceived
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
