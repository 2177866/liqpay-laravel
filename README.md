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

```
composer require alyakin/liqpay-laravel
```

Публикация конфигурации:

```
php artisan vendor:publish --tag=liqpay-config
```

## Конфигурация

После публикации файл конфигурации `config/liqpay.php` содержит:

- `public_key` — публичный ключ от LiqPay
- `private_key` — приватный ключ от LiqPay
- `sandbox` — режим песочницы (`true`/`false`)
- `result_url` — ссылка для перенаправления пользователя после оплаты
- `server_url` — ссылка для программного уведомления (webhook)

Все параметры можно переопределить через `.env` файл:

```
LIQPAY_PUBLIC_KEY=your_public_key
LIQPAY_PRIVATE_KEY=your_private_key
LIQPAY_SANDBOX=true
LIQPAY_RESULT_URL="${APP_URL}/billing"
LIQPAY_SERVER_URL="/api/liqpay/webhook"
```

## Использование

### Формирование ссылки для оплаты

```
use LiqPay\Laravel\Services\LiqPay;

$liqpay = app(LiqPay::class);

$url = $liqpay->paymentUrl([
    'amount' => 100,
    'currency' => 'UAH',
    'description' => 'Оплата заказа #1234',
    'order_id' => 'ORDER-1234',
    'result_url' => route('liqpay.result'),
    'server_url' => route('liqpay.webhook'),
]);

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




## Тестирование

```shell
composer test
```

## Лицензия

MIT.
