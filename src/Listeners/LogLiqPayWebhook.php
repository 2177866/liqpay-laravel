<?php

namespace Alyakin\LiqpayLaravel\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpayWebhookReceived;
use Illuminate\Support\Facades\Log;

class LogLiqpayWebhook
{
    public function handle(LiqpayWebhookReceived $event): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/liqpay.log'),
        ])->info(__METHOD__, $event->dto->toArray());
    }
}
