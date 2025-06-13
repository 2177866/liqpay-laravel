<?php

namespace Alyakin\LiqpayLaravel\Events;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

/**
 * Class LiqpayPaymentSucceeded
 * Событие при успешной оплате.
 */
class LiqpayPaymentSucceeded
{
    public function __construct(public readonly LiqpayWebhookDto $dto) {}
}
