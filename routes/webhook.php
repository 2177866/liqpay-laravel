<?php

use Alyakin\LiqPayLaravel\Http\Controllers\LiqPayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/api/liqpay/webhook', [LiqPayWebhookController::class, 'handle'])
    ->name('liqpay.webhook');
