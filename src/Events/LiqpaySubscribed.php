<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpaySubscribed
 * Событие при успешной активации подписки.
 */
class LiqpaySubscribed
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
