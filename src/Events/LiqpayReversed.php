<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayReversed
 * Событие при возврате средств.
 */
class LiqpayReversed
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
