<?php

namespace Tests\Unit;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;
use Alyakin\LiqpayLaravel\Tests\TestCase;

class LiqpayWebhookDtoTest extends TestCase
{
    public function test_from_array(): void
    {
        $dto = LiqpayWebhookDto::fromArray(['status' => 'success', 'amount' => 250]);
        $this->assertSame('success', $dto->status);
        $this->assertEquals(250, $dto->amount);
    }

    public function test_from_array_with_additional_fields(): void
    {
        $dto = LiqpayWebhookDto::fromArray(['status' => 'failure', 'amount' => 500, 'extra_field' => 'value']);
        $this->assertSame('failure', $dto->status);
        $this->assertEquals(500, $dto->amount);

        $this->assertSame('value', $dto->__get('extra_field'));
        // example: $this->assertSame('value', $dto->extra_field);
    }
}
