# liqpay-laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alyakin/liqpay-laravel.svg)](https://packagist.org/packages/alyakin/liqpay-laravel)
[![Downloads](https://img.shields.io/packagist/dt/alyakin/liqpay-laravel.svg)](https://packagist.org/packages/alyakin/liqpay-laravel)
![Laravel](https://img.shields.io/badge/Laravel-10%2B-orange)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![License](https://img.shields.io/badge/license-MIT-brightgreen)

[![PHPUnit](https://github.com/2177866/liqpay-laravel/actions/workflows/phpunit.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/phpunit.yml)
[![Laravel Pint](https://github.com/2177866/liqpay-laravel/actions/workflows/pint.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/pint.yml)
[![Larastan](https://github.com/2177866/liqpay-laravel/actions/workflows/larastan.yml/badge.svg)](https://github.com/2177866/liqpay-laravel/actions/workflows/larastan.yml)


Пакет для интеграции LiqPay в Laravel приложение. Позволяет формировать ссылки для оплаты, подписывать запросы, а также обрабатывать и валидировать входящие webhook-события от LiqPay.

---

## Содержание

- [Требования](#требования)
- [Установка](#установка)
- [Конфигурация](#конфигурация)
- [Использование](#использование)
  - [Формирование ссылки для оплаты](#формирование-ссылки-для-оплаты)
  - [Обработка webhook от LiqPay](#обработка-webhook-от-liqpay)
- [Тестирование](#тестирование)
- [Лицензия](#лицензия)


## Требования

- PHP 8.1+
- Laravel 9+

## Установка

Добавьте пакет через Composer:

```shell
composer require alyakin/liqpay-laravel
```

Публикация конфигурации:

```shell
php artisan vendor:publish --tag=liqpay-config
```

## Конфигурация

После публикации файл конфигурации `config/liqpay.php` содержит:

- `public_key` — публичный ключ от LiqPay
- `private_key` — приватный ключ от LiqPay
- `result_url` — ссылка для перенаправления пользователя после оплаты
- `server_url` — ссылка для программного уведомления (webhook)

Все параметры можно переопределить через `.env` файл:

```shell
LIQPAY_PUBLIC_KEY=your_public_key
LIQPAY_PRIVATE_KEY=your_private_key
LIQPAY_RESULT_URL="${APP_URL}/billing"
LIQPAY_SERVER_URL="/api/liqpay/webhook"
```

## Использование

### Формирование ссылки для оплаты

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
    'description' => 'Оплата заказа #'.($a = rand(1000,9999)),
    'language' => 'ua',
    'order_id' => 'ORDER-'.$a,
    'result_url' => config('liqpay.result_url'),
    'server_url' => config('app.url').config('liqpay.server_url'),
]));

return redirect($url);
```

### Обработка webhook от LiqPay

Пакет автоматически регистрирует маршрут `/api/liqpay/webhook` (маршрут из конфига)  и включает в себя обработчик поступивших запросов.

при срабатывании webhook вызываются события:

- `LiqpayWebhookReceived` - возникает при получении ЛЮБОГО webhook от LiqPay

после вызова общего события будут вызваны события соответствующие статусам:

- `LiqpayPaymentFailed` - возникает при неудачной оплате
- `LiqpayPaymentSucceeded` - возникает при успешной оплате
- `LiqpayPaymentWaiting` - возникает при ожидании оплаты
- `LiqpayReversed` - возникает при отмене платежа
- `LiqpaySubscribed` - возникает при подписке на платежи
- `LiqpayUnsubscribed` - возникает при отписке от платежей

Для обработки этих событий в вашем Laravel приложении, вы можете зарегистрировать соответствующие слушатели событий.

Пример регистрации слушателя для события `LiqpayPaymentSucceeded`:

```php
namespace App\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;

class HandleLiqpayPaymentSucceeded
{
    public function handle(LiqpayPaymentSucceeded $event)
    {
        \Log::debug(__method__, $event->dto->toArray());
        // Ваш код обработки успешной оплаты
    }
}
```
Событие имеет свойство `dto`, являющееся [объектом](/src/DTO/LiqPayWebhookDto.php).

Вы также можете включить встроенный обработчик события `LiqpayWebhookReceived` для логирования всех входящих вебхуков, зарегистрировав в `app/Providers/EventServiceProvider.php` в методе `boot` следующим образом:
```php
Event::listen(
    \Alyakin\LiqPayLaravel\Events\LiqpayWebhookReceived::class,
    \Alyakin\LiqPayLaravel\Listeners\LogLiqPayWebhook::class,
);
```


## Тестирование

```shell
composer test
```

## Лицензия

MIT.
