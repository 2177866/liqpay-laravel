<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayUnsubscribed
 * Событие при отключении подписки.
 */
class LiqpayUnsubscribed
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
