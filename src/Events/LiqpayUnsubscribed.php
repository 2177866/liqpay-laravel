<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayUnsubscribed
 * Событие при отключении подписки.
 */
class LiqpayUnsubscribed
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
