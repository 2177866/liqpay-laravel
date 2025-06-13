<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayPaymentWaiting
 * Событие при промежуточном статусе: ожидает подтверждения/действия.
 */
class LiqpayPaymentWaiting
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
