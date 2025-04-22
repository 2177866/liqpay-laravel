<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayPaymentWaiting
 * Событие при промежуточном статусе: ожидает подтверждения/действия.
 */
class LiqpayPaymentWaiting
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
