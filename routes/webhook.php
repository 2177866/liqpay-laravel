<?php

use Alyakin\LiqpayLaravel\Http\Controllers\LiqpayWebhookController;
use Illuminate\Support\Facades\Route;

/** @var string $server_url */
$server_url = config('liqpay.server_url');

Route::post($server_url, [LiqpayWebhookController::class, 'handle'])
    ->name('liqpay.webhook');
