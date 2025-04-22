<?php

namespace Tests\Unit;

use Alyakin\LiqPayLaravel\DTO\LiqPayRequestDto;
use Tests\TestCase;

class LiqPayRequestDtoTest extends TestCase
{
    public function test_array_serialization(): void
    {
        $dto = new LiqPayRequestDto(
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
