<?php

namespace Alyakin\LiqPayLaravel\Contracts;

use Alyakin\LiqPayLaravel\DTO\LiqPayRequestDto;
use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;

interface LiqPayServiceInterface
{
    public function getPaymentUrl(LiqPayRequestDto $dto): string;

    public function decodeWebhook(string $data, string $signature): LiqPayWebhookDto;
}
