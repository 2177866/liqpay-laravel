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
  - [Generating a payment link](#generating-a-payment-link)
  - [Handling webhook from Liqpay](#handling-webhook-from-liqpay-events)
  - [Subscription support](#-subscription-support)
  - [Importing subscriptions from the archive](#-importing-subscriptions-from-the-archive)
  - [Managing subscriptions manually](#-managing-subscriptions-manually)
- [Localization &amp; Translations](#localization--translations)
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

Publishing the configuration:

```shell
php artisan vendor:publish --tag=liqpay-config
php artisan vendor:publish --tag=liqpay-migrations
```

Check the created configuration and migration files, make changes, and then run

```shell
php artisan migrate
```

## Configuration

After publishing, the configuration file `config/liqpay.php` contains:

- `public_key` — public key from Liqpay
- `private_key` — private key from Liqpay
- `result_url` — link for redirecting the user after payment
- `server_url` — link for programmatic notification (webhook)

All parameters can be overridden through the `.env` file:

```shell
LIQPAY_PUBLIC_KEY=your_public_key
LIQPAY_PRIVATE_KEY=your_private_key
LIQPAY_RESULT_URL="${APP_URL}/billing"
LIQPAY_SERVER_URL="/api/liqpay/webhook"
```

## Usage

### Generating a payment link

```php
use Alyakin\LiqpayLaravel\Contracts\LiqpayServiceInterface as Liqpay;
use Alyakin\LiqpayLaravel\DTO\LiqpayRequestDto;

$liqpay = app(Liqpay::class);

$url = $liqpay->getPaymentUrl(LiqpayRequestDto::fromArray([
    'version' => 3,
    'public_key' => config('liqpay.public_key'),
    'action' => 'pay',
    'amount' => 100,
    'currency' => 'UAH',
    'description' => 'Payment for order #'.($a = rand(1000,9999)),
    'language' => 'ua',
    'order_id' => 'ORDER-'.$a,
    'result_url' => config('liqpay.result_url'),
    'server_url' => config('app.url').config('liqpay.server_url'),
]));

return redirect($url);
```

### Handling webhook from Liqpay (events)

The package automatically registers the route `/api/liqpay/webhook` (the route from the config) and includes a handler for incoming requests.

When the webhook is triggered, the following events are called:

- `LiqpayWebhookReceived` - occurs when ANY webhook is received from Liqpay

After the general event is triggered, events corresponding to the statuses will be called:

- `LiqpayPaymentFailed` - occurs when payment fails
- `LiqpayPaymentSucceeded` - occurs when payment is successful
- `LiqpayPaymentWaiting` - occurs when payment is pending
- `LiqpayReversed` - occurs when payment is canceled
- `LiqpaySubscribed` - occurs when subscribing to payments
- `LiqpayUnsubscribed` - occurs when unsubscribing from payments

To handle these events in your Laravel application, you can register the corresponding event listeners. Pay special attention to [the package&#39;s behavior in case of errors in event handlers](docs/EVENTS.md).

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

The event has a property `dto`, which is [an object](/src/DTO/LiqpayWebhookDto.php).

You can also enable the built-in event handler `LiqpayWebhookReceived` for logging all incoming webhooks by registering it in `app/Providers/EventServiceProvider.php` in the `boot` method as follows:

```php
Event::listen(
    \Alyakin\LiqpayLaravel\Events\LiqpayWebhookReceived::class,
    \Alyakin\LiqpayLaravel\Listeners\LogLiqpayWebhook::class,
);
```

### 📦 Subscription support

The package supports automatic subscription registration via webhook (`action: subscribe`) and deactivation (`status: unsubscribed`).

### 📥 Importing subscriptions from the archive

To import and synchronize Liqpay subscriptions in bulk, use the built-in Artisan command:

```shell
php artisan liqpay:sync-subscriptions [--from=YYYY-MM-DD] [--to=YYYY-MM-DD] [--restart]
```

- By default, the command imports the archive for the past month.
- Supports safe resuming: processing progress is saved in cache and can recover from interruptions.
- The `--restart` flag resets progress and restarts the import from scratch.
- Archive processing is memory efficient: CSV is streamed and never fully loaded into memory.

**Example:**
```shell
php artisan liqpay:sync-subscriptions --from=2024-01-01 --to=2024-06-30
```

The archive is downloaded directly from the Liqpay API, and large datasets are handled reliably, even with failures or restarts.

---

**Recommended for initial data loading.**


### 🔧 Managing subscriptions manually

```php
$liqpay->unsubscribe('ORDER-123');
$liqpay->subscribeUpdate(new LiqpaySubscriptionDto(...));
```

## Localization & Translations

All messages support translations out of the box (en/ru/uk).
For best practices and details on customizing translations, see [TRANSLATIONS.md](./docs/TRANSLATIONS.md).

## Testing

All tests can be found in the folder with [`tests`](/tests/)

To run the tests, use the command
```shell
composer test
```

## License

This package is distributed under the [MIT License](/LICENSE).
