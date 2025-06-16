<?php

namespace Alyakin\LiqpayLaravel\Tests;

use Alyakin\LiqpayLaravel\Helpers\LiqpaySignatureValidator;
use Alyakin\LiqpayLaravel\LiqpayServiceProvider;
use Alyakin\LiqpayLaravel\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMockingConsoleOutput();
    }

    protected function getPackageProviders($app)
    {
        return [
            LiqpayServiceProvider::class,
            EventServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $path = __DIR__.'/../database/migrations';
        $this->loadMigrationsFrom($path);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('liqpay.public_key', 'test_public');
        $app['config']->set('liqpay.private_key', 'test_private');
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('favorites.enabled', true);
    }

    /**
     * Генерирует корректный webhook-запрос Liqpay с подписью
     *
     * @param  array<string, mixed>  $overrides
     * @return array{data: string, signature: string}
     */
    protected function makeWebhookRequest(array $overrides = []): array
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
