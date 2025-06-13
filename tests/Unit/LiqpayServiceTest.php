<?php

namespace Tests\Unit;

use Alyakin\LiqpayLaravel\DTO\LiqpayRequestDto;
use Alyakin\LiqpayLaravel\Services\LiqpayService;
use Alyakin\LiqpayLaravel\Tests\TestCase;

class LiqpayServiceTest extends TestCase
{
    public function test_it_generates_payment_url(): void
    {
        $dto = new LiqpayRequestDto(
            version: '3',
            public_key: 'test_public',
            action: 'pay',
            amount: 100,
            currency: 'UAH',
            order_id: 'ORD123',
            description: 'Test'
        );

        $service = new LiqpayService;
        $url = $service->getPaymentUrl($dto);

        $this->assertStringContainsString('https://www.liqpay.ua/api/3/checkout', $url);
        $this->assertStringContainsString('data=', $url);
        $this->assertStringContainsString('signature=', $url);
    }
}
