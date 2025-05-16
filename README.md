# Laravel package for LiqPay integration (liqpay-laravel)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alyakin/liqpay-laravel.svg)](https://packagist.org/packages/alyakin/liqpay-laravel)
[![Downloads](https://img.shields.io/packagist/dt/alyakin/liqpay-laravel.svg)](https://packagist.org/packages/alyakin/liqpay-laravel)
![Laravel](https://img.shields.io/badge/Laravel-10%2B-orange)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![License](https://img.shields.io/badge/license-MIT-brightgreen)

[![PHPUnit](https://github.com/2177866/liqpay-laravel/actions/workflows/phpunit.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/phpunit.yml)
[![Laravel Pint](https://github.com/2177866/liqpay-laravel/actions/workflows/pint.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/pint.yml)
[![Larastan](https://github.com/2177866/liqpay-laravel/actions/workflows/larastan.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/larastan.yml)


Package for integrating LiqPay into Laravel application. It allows generating payment links, signing requests, and handling incoming webhook events from LiqPay.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Generating Payment Link](#generating-payment-link)
    - [Handling LiqPay Webhook](#handling-liqpay-webhook)
- [Testing](#testing)
- [License](#license)


## Requirements
- PHP 8.1+
- Laravel 9+

## Installation

Add the package via Composer:
```shell
composer require alyakin/liqpay-laravel
```

Publishing Configuration:

```shell
php artisan vendor:publish --tag=liqpay-config
```

## Configuration

After publishing, the configuration file `config/liqpay.php` contains:

- `public_key` — public key from LiqPay
- `private_key` — private key from LiqPay
- `result_url` — URL to redirect the user after payment
- `server_url` — URL for programmatic notifications (webhook)

All parameters can be overridden via the `.env` file:

```shell
LIQPAY_PUBLIC_KEY=your_public_key
LIQPAY_PRIVATE_KEY=your_private_key
LIQPAY_RESULT_URL="${APP_URL}/billing"
LIQPAY_SERVER_URL="/api/liqpay/webhook"
```

## Usage

### Generating Payment Link

```php
use Alyakin\LiqPayLaravel\Contracts\LiqPayServiceInterface as LiqPay;
use Alyakin\LiqPayLaravel\DTO\LiqPayRequestDto;

$liqpay = app(LiqPay::class);

$url = $liqpay->getPaymentUrl(LiqPayRequestDto::fromArray([
    'version' => 3,
    'public_key' => config('liqpay.public_key'),
    'action' => 'pay',
    'amount' => 100,
    'currency' => 'UAH',
    'description' => 'Payment #'.($a = rand(1000,9999)),
    'language' => 'ua',
    'order_id' => 'ORDER-'.$a,
    'result_url' => config('liqpay.result_url'),
    'server_url' => config('app.url').config('liqpay.server_url'),
]));

return redirect($url);
```

### Handling LiqPay Webhook

The package automatically registers the route `/api/liqpay/webhook` (route from the config) and includes a handler for incoming requests.

When the webhook is triggered, the following events are fired:

- `LiqpayWebhookReceived` - occurs when ANY webhook from LiqPay is received

After the general event is called, events corresponding to statuses will be triggered:

- `LiqpayPaymentFailed` - occurs on payment failure
- `LiqpayPaymentSucceeded` - occurs on successful payment
- `LiqpayPaymentWaiting` - occurs when payment is pending
- `LiqpayReversed` - occurs when a payment is reversed
- `LiqpaySubscribed` - occurs when subscribed to payments
- `LiqpayUnsubscribed` - occurs when unsubscribed from payments

To handle these events in your Laravel application, you can register corresponding event listeners.

Example of registering a listener for the `LiqpayPaymentSucceeded` event:

```php
namespace App\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;

class HandleLiqpayPaymentSucceeded
{
    public function handle(LiqpayPaymentSucceeded $event)
    {

        \Log::debug(__method__, $event->dto->toArray());
        // Your code for handling successful payment
    }
}
```

The event has a property `dto`, which is an [object](/src/DTO/LiqPayWebhookDto.php).

You can also enable the built-in event handler `LiqpayWebhookReceived` to log all incoming webhooks by registering in `app/Providers/EventServiceProvider.php` in the `boot` method as follows:

```php
Event::listen(
    \Alyakin\LiqPayLaravel\Events\LiqpayWebhookReceived::class,
    \Alyakin\LiqPayLaravel\Listeners\LogLiqPayWebhook::class,
);
```


## Testing

All tests can be found in the folder with [`tests`](/tests/)

To run the tests, use the command
```shell
composer test
```

## License

This package is distributed under the [MIT License](/LICENSE).
