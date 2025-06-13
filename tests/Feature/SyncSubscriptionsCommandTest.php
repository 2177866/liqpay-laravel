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

    private string $csvFixture = <<<'CSV'
action,status,order_id,amount,currency,description,liqpay_order_id,payment_id,create_date,info
subscribe,subscribed,ORD-1,100,UAH,Test desc,LQ-ORD-1,PM-1,1717777333000,"{""email"": ""user1@example.com""}"
subscribe,unsubscribed,ORD-1,100,UAH,Test desc,LQ-ORD-1,PM-1,1717779999000,"{""email"": ""user1@example.com""}"
regular,success,ORD-1,100,UAH,Test desc,LQ-ORD-1,PM-2,1717888888000,"{""email"": ""user1@example.com""}"
subscribe,subscribed,ORD-2,200,USD,Second,LQ-ORD-2,PM-3,1717888888000,"{""email"": ""user2@example.com""}"
CSV;

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

    public function test_first_run_imports_entire_csv_and_clears_cache(): void
    {

        $this->mockLiqpayServiceCsv();
        $this->assertDatabaseMissing('liqpay_subscriptions', ['order_id' => 'ORD-1']);

        $this->artisan('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);
        // $output = Artisan::output();

        // Проверяем что обе подписки созданы и обновлены корректно
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
        $this->assertNull(Cache::get('liqpay:sync:line'));
        $this->assertCount(0, Storage::disk('local')->allFiles('liqpay-archive'));
    }

    public function test_resume_from_progress(): void
    {
        $this->mockLiqpayServiceCsv();
        // Создаем кеш как будто импорт уже обработал первую строку
        $filename = 'liqpay-archive/test_resume.csv';
        Storage::put($filename, $this->csvFixture);
        Cache::put('liqpay:sync:file', $filename, 3600);
        Cache::put('liqpay:sync:line', 1, 3600);

        Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);

        // Должны быть обработаны оставшиеся строки
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
        $this->mockLiqpayServiceCsv();
        // Предположим был старый файл и прогресс
        $filename = 'liqpay-archive/test_restart.csv';
        Storage::put($filename, $this->csvFixture);
        Cache::put('liqpay:sync:file', $filename, 3600);
        Cache::put('liqpay:sync:line', 2, 3600);

        Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
            '--restart' => true,
        ]);

        // Всё обработано с нуля, кеша нет
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => 'ORD-2',
            'status' => 'active',
        ]);
        $this->assertNull(Cache::get('liqpay:sync:file'));
        $this->assertCount(0, Storage::disk('local')->allFiles('liqpay-archive'));
    }

    public function test_handles_broken_csv_gracefully(): void
    {
        $brokenCsv = "action,status,order_id\nsubscribe,subscribed,ORD-1\nbroken,line";
        $this->mockLiqpayServiceCsv($brokenCsv);
        $result = Artisan::call('liqpay:sync-subscriptions', [
            '--from' => '2024-01-01',
            '--to' => '2024-01-31',
        ]);
        $this->assertNotEquals(0, $result, 'Should not exit successfully');
        // Кеш прогресса зафиксирован на ошибочной строке
        $this->assertNotNull(Cache::get('liqpay:sync:line'));
    }

    private function mockLiqpayServiceCsv(?string $csv = null): void
    {
        $mock = Mockery::mock(LiqpayService::class);
        // @phpstan-ignore-next-line
        $mock->shouldReceive('api')->andReturn($csv ?? $this->csvFixture);
        // @phpstan-ignore-next-line
        $this->app->instance(LiqpayService::class, $mock);
    }
}
