<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayPaymentFailed
 * Событие при неуспешной оплате или ошибке.
 */
class LiqpayPaymentFailed
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
