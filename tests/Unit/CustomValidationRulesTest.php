<?php

namespace Tests\Unit;

use Alyakin\LiqpayLaravel\Helpers\LiqpaySignatureValidator;
use Alyakin\LiqpayLaravel\Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class CustomValidationRulesTest extends TestCase
{
    public function test_is_base64_passes(): void
    {
        $value = base64_encode('test');
        $validator = Validator::make(['value' => $value], ['value' => 'is_base64']);
        $this->assertFalse($validator->fails());
    }

    public function test_liqpay_signature_rule(): void
    {
        $payload = ['order_id' => '123'];

        $json = json_encode($payload);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed');
        }

        $data = base64_encode($json);
        /** @var string $privateKey */
        $privateKey = config('liqpay.private_key');

        $signature = LiqpaySignatureValidator::generate($data, $privateKey);

        $validator = Validator::make([
            'data' => $data,
            'signature' => $signature,
        ], [
            'data' => 'required|is_base64',
            'signature' => 'required|liqpay_signature:data',
        ]);

        $this->assertFalse($validator->fails());
    }
}
