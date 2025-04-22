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
composer require 2177866/liqpay-laravel
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

Все параметры можно переопределить через `.env` файл:

```
LIQPAY_PUBLIC_KEY=your_public_key
LIQPAY_PRIVATE_KEY=your_private_key
LIQPAY_SANDBOX=true
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

Создайте маршрут в `routes/web.php` или `api.php`:

```
Route::post('/liqpay/webhook', WebhookController::class);
```

Контроллер может выглядеть так:

```
use LiqPay\Laravel\Http\Requests\LiqPayWebhookRequest;

class WebhookController extends Controller
{
    public function __invoke(LiqPayWebhookRequest $request): Response
    {
        $dto = $request->toDto();

        // Логика обработки оплаты по $dto->order_id, $dto->status и др.

        return response('OK');
    }
}
```

## Тестирование

```
composer test
```

## Лицензия

MIT. См. файл LICENSE (будет добавлен).
