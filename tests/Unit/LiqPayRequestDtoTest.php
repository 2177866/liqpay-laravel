<?php

namespace Tests\Unit;

use Alyakin\LiqpayLaravel\DTO\LiqpayRequestDto;
use Alyakin\LiqpayLaravel\Tests\TestCase;

class LiqpayRequestDtoTest extends TestCase
{
    public function test_array_serialization(): void
    {
        $dto = new LiqpayRequestDto(
            version: '3',
            public_key: 'pk',
            action: 'pay',
            amount: 100,
            currency: 'UAH',
            order_id: '1',
            description: 'desc'
        );

        $array = $dto->toArray();
        $this->assertSame('pay', $array['action']);
    }
}
