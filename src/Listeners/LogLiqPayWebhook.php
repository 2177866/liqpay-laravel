<?php

namespace Alyakin\LiqPayLaravel\Listeners;

use Alyakin\LiqPayLaravel\Events\LiqpayWebhookReceived;
use Illuminate\Support\Facades\Log;

class LogLiqPayWebhook
{
    public function handle(LiqpayWebhookReceived $event): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/liqpay.log'),
        ])->info(__METHOD__, $event->dto->toArray());
    }
}
