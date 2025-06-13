<?php

namespace Alyakin\LiqpayLaravel\Tests\Feature;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentFailed;
use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;
use Alyakin\LiqpayLaravel\Events\LiqpayPaymentWaiting;
use Alyakin\LiqpayLaravel\Events\LiqpayReversed;
use Alyakin\LiqpayLaravel\Events\LiqpayWebhookReceived;
use Alyakin\LiqpayLaravel\Helpers\LiqpaySignatureValidator;
use Alyakin\LiqpayLaravel\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class LiqpayWebhookEventsTest extends TestCase
{
    protected string $route;

    protected function setUp(): void
    {
        parent::setUp();
        $this->route = config('liqpay.server_url', '/api/liqpay/webhook').'';
    }

    public function test_webhook_received_event_dispatched(): void
    {
        Event::fake();
        $response = $this->postJson($this->route, $this->makeWebhookRequest());
        $response->assertOk();
        Event::assertDispatched(LiqpayWebhookReceived::class);
    }

    public function test_payment_succeeded_event_dispatched(): void
    {
        Event::fake();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'status' => 'success',
        ]));
        $response->assertOk();
        Event::assertDispatched(LiqpayPaymentSucceeded::class);
    }

    public function test_subscribed_event_creates_active_subscription(): void
    {

        $orderId = Str::uuid()->toString();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
        ]));
        $response->assertOk();

        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'status' => 'active',
        ]);
    }

    public function test_regular_payment_event_updates_subscription(): void
    {

        $orderId = Str::uuid()->toString();
        $paymentId = rand(1000, 9999);
        // Сначала подписка (active)
        $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
        ]));

        // Первый регулярный платёж
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'regular',
            'status' => 'success',
            'payment_id' => $paymentId,
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'last_payment_id' => $paymentId, // замените на актуальный ID платежа
            // здесь можно добавить проверки last_paid_at, last_payment_id и др.
        ]);
    }

    public function test_unsubscribed_event_deactivates_subscription(): void
    {

        $orderId = Str::uuid()->toString();
        // Сначала подписка (active)
        $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
        ]));

        // Деактивация
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'unsubscribed',
        ]));
        $response->assertOk();

        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'status' => 'inactive',
        ]);
    }

    public function test_payment_failed_event_dispatched(): void
    {
        Event::fake();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'status' => 'failure',
        ]));
        $response->assertOk();
        Event::assertDispatched(LiqpayPaymentFailed::class);
    }

    public function test_payment_waiting_event_dispatched(): void
    {
        Event::fake();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'status' => 'invoice_wait',
        ]));
        $response->assertOk();
        Event::assertDispatched(LiqpayPaymentWaiting::class);
    }

    public function test_reversed_event_dispatched(): void
    {
        Event::fake();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'status' => 'reversed',
        ]));
        $response->assertOk();
        Event::assertDispatched(LiqpayReversed::class);
    }

    public function test_log_liqpay_webhook_handler_creates_log_file(): void
    {
        // Подключаем log-listener вручную, если не подключён по умолчанию:
        \Illuminate\Support\Facades\Event::listen(
            \Alyakin\LiqpayLaravel\Events\LiqpayWebhookReceived::class,
            [\Alyakin\LiqpayLaravel\Listeners\LogLiqpayWebhook::class, 'handle']
        );

        $logPath = storage_path('logs/liqpay.log');
        @unlink($logPath);

        $orderId = \Illuminate\Support\Str::uuid()->toString();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'pay',
            'status' => 'success',
            'amount' => 777,
            'currency' => 'UAH',
        ]));
        $response->assertOk();

        $this->assertFileExists($logPath);
        $log = file_get_contents($logPath).'';

        // Проверяем что order_id и/или amount и action есть в логе
        $this->assertStringContainsString($orderId, $log);
        $this->assertStringContainsString('"amount":777', $log);
        $this->assertStringContainsString('"action":"pay"', $log);
    }

    public function test_custom_listener_is_called(): void
    {
        // Не вызывайте fakeExcept, если нужен чистый Event реестр!
        $called = false;
        Event::listen(
            \Alyakin\LiqpayLaravel\Events\LiqpaySubscribed::class,
            function ($event) use (&$called) {
                $called = true;
            }
        );

        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'action' => 'subscribe',
            'status' => 'subscribed',
        ]));

        $response->assertOk();
        $this->assertTrue($called, 'Custom listener was not called');
    }

    /**
     * Генерирует корректный webhook-запрос Liqpay с подписью
     *
     * @param  array<string, mixed>  $overrides
     * @return array{data: string, signature: string}
     */
    private function makeWebhookRequest(array $overrides = []): array
    {
        $payload = array_merge([
            'order_id' => Str::uuid()->toString(),
            'action' => 'pay',
            'status' => 'success',
            'amount' => 100,
            'currency' => 'UAH',
            'info' => json_encode(['email' => 'test@example.com']),
            'create_date' => ((int) now()->timestamp) * 1000,
            // добавьте другие поля, если они обязательны для DTO/Request
        ], $overrides);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed');
        }
        $data = base64_encode($json);

        /** @var string $privateKey */
        $privateKey = config('liqpay.private_key').'';
        $signature = LiqpaySignatureValidator::generate($data, $privateKey);

        return [
            'data' => $data,
            'signature' => $signature,
        ];
    }
}
