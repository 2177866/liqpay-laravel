<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayPaymentFailed
 * Событие при неуспешной оплате или ошибке.
 */
class LiqpayPaymentFailed
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
