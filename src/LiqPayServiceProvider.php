<?php

namespace Alyakin\LiqpayLaravel;

use Alyakin\LiqpayLaravel\Contracts\LiqpayServiceInterface;
use Alyakin\LiqpayLaravel\Services\LiqpayService;
use Alyakin\LiqpayLaravel\Validators\LiqpayCustomValidators;
use Illuminate\Support\ServiceProvider;

class LiqpayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/liqpay.php', 'liqpay');
        $this->app->bind(LiqpayServiceInterface::class, LiqpayService::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'liqpay-laravel');
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');

        $this->publishes([
            __DIR__.'/../config/liqpay.php' => config_path('liqpay.php'),
        ], 'liqpay-config');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/liqpay-laravel'),
        ], 'liqpay-lang');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_liqpay_subscriptions_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_liqpay_subscriptions_table.php'),
            ], 'liqpay-migrations');

            $this->commands([
                \Alyakin\LiqpayLaravel\Commands\SyncSubscriptionsCommand::class,
            ]);

        }

        LiqpayCustomValidators::register();

        // Регистрируем события пакета
        $this->app->register(\Alyakin\LiqpayLaravel\Providers\EventServiceProvider::class);
    }
}
