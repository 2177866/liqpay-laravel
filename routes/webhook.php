<?php

use Alyakin\LiqPayLaravel\Http\Controllers\LiqPayWebhookController;
use Illuminate\Support\Facades\Route;

/** @var string $server_url */
$server_url = config('liqpay.server_url');

Route::post($server_url, [LiqPayWebhookController::class, 'handle'])
    ->name('liqpay.webhook');
