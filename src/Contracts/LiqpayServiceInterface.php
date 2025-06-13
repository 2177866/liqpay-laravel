<?php

namespace Alyakin\LiqpayLaravel\Contracts;

use Alyakin\LiqpayLaravel\DTO\LiqpayRequestDto;
use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;

interface LiqpayServiceInterface
{
    public function getPaymentUrl(LiqpayRequestDto $dto): string;

    public function decodeWebhook(string $data, string $signature): LiqpayWebhookDto;
}
