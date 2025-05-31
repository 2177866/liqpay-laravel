<?php

namespace Alyakin\LiqPayLaravel;

use Alyakin\LiqPayLaravel\Contracts\LiqPayServiceInterface;
use Alyakin\LiqPayLaravel\Services\LiqPayService;
use Alyakin\LiqPayLaravel\Validators\LiqPayCustomValidators;
use Illuminate\Support\ServiceProvider;

class LiqPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/liqpay.php', 'liqpay');

        $this->app->bind(LiqPayServiceInterface::class, LiqPayService::class);
    }

    public function boot(): void
    {
        // $this->publishes([
        //     __DIR__.'/../database/migrations/create_liqpay_logs_table.php.stub' =>
        //     database_path('migrations/' . date('Y_m_d_His', time()) . '_create_liqpay_logs_table.php'),
        // ], 'liqpay-migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');

        $this->publishes([
            __DIR__.'/../config/liqpay.php' => config_path('liqpay.php'),
        ], 'liqpay-config');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Database/migrations/create_liqpay_subscriptions_table.php.stub' =>
                    database_path('migrations/'.date('Y_m_d_His').'_create_liqpay_subscriptions_table.php'),
            ], 'liqpay-migrations');
        }

        LiqPayCustomValidators::register();
    }
}
