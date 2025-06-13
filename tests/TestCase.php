<?php

namespace Alyakin\LiqpayLaravel\Tests;

use Alyakin\LiqpayLaravel\LiqpayServiceProvider;
use Alyakin\LiqpayLaravel\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
