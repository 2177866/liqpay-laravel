<?php

namespace Alyakin\LiqpayLaravel\Tests\Feature;

use Alyakin\LiqpayLaravel\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class LiqpayEventHandlersTest extends TestCase
{
    protected string $route;

    protected function setUp(): void
    {
        parent::setUp();
        // Добавляем необходимые кастомные поля для этого тест-кейса

        \Illuminate\Support\Facades\Schema::table('liqpay_subscriptions', function ($table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('custom_col')->nullable();
            $table->boolean('observer_flag')->nullable();
        });

        $this->route = config('liqpay.server_url', '/api/liqpay/webhook').'';
    }

    /************************************************************ */

    public function test_subscription_before_save_event_modifies_fields(): void
    {
        Event::listen(
            \Alyakin\LiqpayLaravel\Events\LiqpaySubscriptionBeforeSave::class,
            [$this, 'changeSubscriptionBeforeSave']
        );

        $orderId = \Illuminate\Support\Str::uuid()->toString();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
            'info' => json_encode(['user_id' => 42, 'custom_col' => 'ABC']),
        ]));
        $response->assertOk();

        // Проверка что СТАНДАРТНОЕ событие было вызвано и данные сохранены
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'status' => 'active',
        ]);

        // Изменения внесенные в обработчике события
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'user_id' => 42,
            'custom_col' => 'ABC',
        ]);
    }

    public function test_observer_flag_set_on_saving(): void
    {
        \Alyakin\LiqpayLaravel\Models\LiqpaySubscription::saving(function ($model) {
            $model->observer_flag = true;
        });

        $orderId = \Illuminate\Support\Str::uuid()->toString();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
        ]));
        $response->assertOk();

        // Проверка что СТАНДАРТНОЕ событие было вызвано и данные сохранены
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'status' => 'active',
        ]);

        // Проверка что observer_flag установлен
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'observer_flag' => true,
        ]);
    }

    public function test_subscription_fields_and_observer_flag_work_together(): void
    {
        // Событие и observer одновременно
        Event::listen(
            \Alyakin\LiqpayLaravel\Events\LiqpaySubscriptionBeforeSave::class,
            function ($event) {
                $event->subscription->user_id = 123;
                $event->subscription->custom_col = 'ASD';
            }
        );
        \Alyakin\LiqpayLaravel\Models\LiqpaySubscription::saving(function ($model) {
            $model->observer_flag = true;
        });

        $orderId = \Illuminate\Support\Str::uuid()->toString();
        $response = $this->postJson($this->route, $this->makeWebhookRequest([
            'order_id' => $orderId,
            'action' => 'subscribe',
            'status' => 'subscribed',
            'info' => json_encode(['user_id' => 777, 'custom_col' => 'XYZ']),
        ]));
        $response->assertOk();

        // Проверка что СТАНДАРТНОЕ событие было вызвано и данные сохранены
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'status' => 'active',
        ]);

        // Проверка что observer_flag установлен
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'observer_flag' => true,
        ]);

        // Изменения внесенные в обработчике события
        $this->assertDatabaseHas('liqpay_subscriptions', [
            'order_id' => $orderId,
            'user_id' => 123,
            'custom_col' => 'ASD',
        ]);

    }

    /************************************************************ */

    public static function changeSubscriptionBeforeSave(\Alyakin\LiqpayLaravel\Events\LiqpaySubscriptionBeforeSave $event): void
    {
        /** @var array<string, string|int> $info */
        $info = $event->subscription->info;
        $context = $event->context;

        if (is_array($info)) {
            if (isset($info['user_id'])) {
                // @phpstan-ignore-next-line
                $event->subscription->user_id = $info['user_id'];
            }
            if (isset($info['custom_col'])) {
                // @phpstan-ignore-next-line
                $event->subscription->custom_col = $info['custom_col'];
            }
        }

        // @phpstan-ignore-next-line
        $info['phone'] = $context['payment']->customer ?? null;
        $event->subscription->info = $info;
    }
}
