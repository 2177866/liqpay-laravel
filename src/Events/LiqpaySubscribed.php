<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpaySubscribed
 * Событие при успешной активации подписки.
 */
class LiqpaySubscribed
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
