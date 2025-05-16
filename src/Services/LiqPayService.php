<?php

namespace Alyakin\LiqPayLaravel\Services;

use Alyakin\LiqPayLaravel\Contracts\LiqPayServiceInterface;
use Alyakin\LiqPayLaravel\DTO\LiqPayRequestDto;
use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;
use Alyakin\LiqPayLaravel\Helpers\LiqPaySignatureValidator;
use Illuminate\Support\Facades\Config;

class LiqPayService implements LiqPayServiceInterface
{
    protected string $checkoutUrl = 'https://www.liqpay.ua/api/3/checkout';

    public function getPaymentUrl(LiqPayRequestDto $dto): string
    {
        $json = json_encode($dto->toArray(), JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \UnexpectedValueException('JSON encode failed');
        }

        $data = base64_encode($json);

        /** @var string $privateKey */
        $privateKey = Config::get('liqpay.private_key');

        $signature = LiqPaySignatureValidator::generate($data, $privateKey);

        return "{$this->checkoutUrl}?data={$data}&signature={$signature}";
    }

    public function decodeWebhook(string $data, string $signature): LiqPayWebhookDto
    {
        /** @var string $privateKey */
        $privateKey = Config::get('liqpay.private_key');

        if (! LiqPaySignatureValidator::verify($data, $signature, $privateKey)) {
            throw new \UnexpectedValueException('Invalid LiqPay signature');
        }

        $decoded = json_decode(base64_decode($data), true);

        if (! is_array($decoded)) {
            throw new \UnexpectedValueException('Invalid LiqPay JSON payload');
        }

        /** @var array<string,mixed> $decoded */
        return LiqPayWebhookDto::fromArray($decoded);
    }
}
