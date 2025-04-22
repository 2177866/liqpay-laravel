<?php

namespace Tests\Feature;

use Alyakin\LiqPayLaravel\Events\LiqpayWebhookReceived;
use Alyakin\LiqPayLaravel\Helpers\LiqPaySignatureValidator;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LiqPayWebhookTest extends TestCase
{
    public function test_valid_webhook_triggers_event(): void
    {
        Event::fake();

        $payload = ['status' => 'success', 'action' => 'pay', 'amount' => 100, 'currency' => 'UAH'];

        $json = json_encode($payload);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed');
        }

        $data = base64_encode($json);
        /** @var string $privateKey */
        $privateKey = config('liqpay.private_key');
        $signature = LiqPaySignatureValidator::generate($data, $privateKey);

        $response = $this->postJson('/api/liqpay/webhook', [
            'data' => $data,
            'signature' => $signature,
        ]);

        $response->assertOk();
        Event::assertDispatched(LiqpayWebhookReceived::class);
    }

    public function test_invalid_signature_fails(): void
    {
        $payload = ['status' => 'success'];
        $json = json_encode($payload);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed');
        }

        $data = base64_encode($json);

        $signature = 'invalidsignature';

        $response = $this->postJson('/api/liqpay/webhook', [
            'data' => $data,
            'signature' => $signature,
        ]);

        $response->assertUnprocessable();
    }
}
