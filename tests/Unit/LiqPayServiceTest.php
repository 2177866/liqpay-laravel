<?php

namespace Tests\Unit;

use Alyakin\LiqPayLaravel\DTO\LiqPayRequestDto;
use Alyakin\LiqPayLaravel\Services\LiqPayService;
use Tests\TestCase;

class LiqPayServiceTest extends TestCase
{
    public function test_it_generates_payment_url(): void
    {
        $dto = new LiqPayRequestDto(
            version: '3',
            public_key: 'test_public',
            action: 'pay',
            amount: 100,
            currency: 'UAH',
            order_id: 'ORD123',
            description: 'Test'
        );

        $service = new LiqPayService;
        $url = $service->getPaymentUrl($dto);

        $this->assertStringContainsString('https://www.liqpay.ua/api/3/checkout', $url);
        $this->assertStringContainsString('data=', $url);
        $this->assertStringContainsString('signature=', $url);
    }
}
