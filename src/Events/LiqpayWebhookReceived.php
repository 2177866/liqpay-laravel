<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayWebhookReceived
 * Событие при любом успешном получении webhook от LiqPay.
 */
class LiqpayWebhookReceived
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
