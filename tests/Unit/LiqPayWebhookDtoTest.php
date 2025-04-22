<?php

namespace Tests\Unit;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;
use Tests\TestCase;

class LiqPayWebhookDtoTest extends TestCase
{
    public function test_from_array(): void
    {
        $dto = LiqPayWebhookDto::fromArray(['status' => 'success', 'amount' => 250]);
        $this->assertSame('success', $dto->status);
        $this->assertEquals(250, $dto->amount);
    }
}
