<?php

namespace Tests\Unit;

use Alyakin\LiqPayLaravel\Helpers\LiqPaySignatureValidator;
use Tests\TestCase;

class SignatureValidatorTest extends TestCase
{
    public function test_signature_is_valid(): void
    {
        $payload = ['order_id' => '1'];

        $json = json_encode($payload);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed');
        }

        $data = base64_encode($json);

        /** @var string $privateKey */
        $privateKey = config('liqpay.private_key');

        $signature = LiqPaySignatureValidator::generate($data, $privateKey);

        $this->assertTrue(
            LiqPaySignatureValidator::verify($data, $signature, $privateKey),
        );
    }
}
