<?php

namespace Alyakin\LiqpayLaravel\Tests\Feature;

use Alyakin\LiqpayLaravel\Services\LiqpayService;
use Alyakin\LiqpayLaravel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery;

class SyncSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<array<string, mixed>> */
    private array $jsonFixture = [
        [
            'action' => 'subscribe',
            'status' => 'subscribed',
            'order_id' => 'ORD-1',
            'amount' => 100,
            'currency' => 'UAH',
            'description' => 'Test desc',
            'liqpay_order_id' => 'LQ-ORD-1',
            'payment_id' => 'PM-1',
            'create_date' => 1717777333000,
            'info' => '{"email": "user1@example.com"}',
        ],
        [
            'action' => 'subscribe',
            'status' => 'unsubscribed',
            'order_id' => 'ORD-1',
            'amount' => 100,
            'currency' => 'UAH',
            'description' => 'Test desc',
            'liqpay_order_id' => 'LQ-ORD-1',
            'payment_id' => 'PM-1',
            'create_date' => 1717779999000,
            'info' => '{"email": "user1@example.com"}',
        ],
        [
            'action' => 'regular',
            'status' => 'success',
            'order_id' => 'ORD-1',
            'amount' => 100,
            'currency' => 'UAH',
            'description' => 'Test desc',
            'liqpay_order_id' => 'LQ-ORD-1',
            'payment_id' => 'PM-2',
            'create_date' => 1717888888000,
            'info' => '{"email": "user1@example.com"}',
        ],
        [
            'action' => 'subscribe',
            'status' => 'subscribed',
            'order_id' => 'ORD-2',
            'amount' => 200,
            'currency' => 'USD',
            'description' => 'Second',
            'liqpay_order_id' => 'LQ-ORD-2',
            'payment_id' => 'PM-3',
            'create_date' => 1717888888000,
            'info' => '{"email": "user2@example.com"}',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Cache::flush();
    }

    public function test_can_list_artisan_commands(): void
    {
        $this->artisan('list');
        $output = Artisan::output();

        $this->assertStringContainsString('liqpay:sync-subscriptions', $output);
    }

    public function test_first_run_imports_entire_json_and_clears_cache(): void
    {
        $this->mockLiqpayServiceJson();
        $this->assertDatabaseMissing('liqpay_subscriptions', ['order_id' => 'ORD-1']);

        $this->artisan('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);

        // Проверяем, что обе подписки созданы и обновлены корректно
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-1',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-2',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-1',
            'last_payment_id' => 'PM-2',
        ]);
        // Кеш и временный файл очищены
        $this->assertNull(Cache::get('liqpay:sync:file'));
        $this->assertNull(Cache::get('liqpay:sync:index'));
        $this->assertCount(0, Storage::disk('local')->allFiles('liqpay-archive'));
    }

    public function test_resume_from_progress(): void
    {
        $this->mockLiqpayServiceJson();
        // Создаём кеш, как будто импорт уже обработал первую запись (index = 1)
        $filename = 'liqpay-archive/test_resume.json';
        $this->saveJsonFixture($filename);
        Cache::put('liqpay:sync:file', $filename, 3600);
        Cache::put('liqpay:sync:index', 1, 3600);

        Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);

        // Должны быть обработаны оставшиеся записи
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-1',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-2',
            'status' => 'active',
        ]);
        $this->assertNull(Cache::get('liqpay:sync:file'));
    }

    public function test_restart_flag_clears_cache_and_starts_fresh(): void
    {
        $this->mockLiqpayServiceJson();
        $filename = 'liqpay-archive/test_restart.json';
        $this->saveJsonFixture($filename);
        Cache::put('liqpay:sync:file', $filename, 3600);
        Cache::put('liqpay:sync:index', 2, 3600);

        Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
            '--restart' => true,
        ]);

        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-2',
            'status' => 'active',
        ]);
        $this->assertNull(Cache::get('liqpay:sync:file'));
        $this->assertCount(0, Storage::disk('local')->allFiles('liqpay-archive'));
    }

    public function test_handles_broken_json_gracefully(): void
    {
        $brokenJson = '{"data": [ { "action": "subscribe", "order_id": "ORD-1" }, BROKEN ]}';
        $this->mockLiqpayServiceJson($brokenJson);

        $result = Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);
        $this->assertNotEquals(0, $result, 'Should not exit successfully');
        // Кеш прогресса зафиксирован на ошибочном индексе
        $this->assertNotNull(Cache::get('liqpay:sync:index'));
    }

    /**
     * Мокаем сервис, чтобы возвращать JSON-данные вместо CSV.
     */
    private function mockLiqpayServiceJson(?string $json = null): void
    {
        $mock = Mockery::mock(LiqpayService::class);
        $response = $json ?? json_encode(['result' => 'success', 'data' => $this->jsonFixture]);
        // @phpstan-ignore-next-line
        $mock->shouldReceive('api')->andReturn($response);
        // @phpstan-ignore-next-line
        $this->app->instance(LiqpayService::class, $mock);
    }

    /**
     * Сохраняет актуальный JSON-фрагмент в storage/app.
     */
    private function saveJsonFixture(string $filename): void
    {
        $data = json_encode(['result' => 'success', 'data' => $this->jsonFixture], JSON_PRETTY_PRINT);
        Storage::put($filename, ''.$data);
    }
}
