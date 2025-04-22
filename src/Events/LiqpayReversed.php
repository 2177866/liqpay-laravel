<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayReversed
 * Событие при возврате средств.
 */
class LiqpayReversed
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
