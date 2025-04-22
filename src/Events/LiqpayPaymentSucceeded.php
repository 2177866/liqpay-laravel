<?php

namespace Alyakin\LiqPayLaravel\Events;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

/**
 * Class LiqpayPaymentSucceeded
 * Событие при успешной оплате.
 */
class LiqpayPaymentSucceeded
{
    public function __construct(public readonly LiqPayWebhookDto $dto) {}
}
