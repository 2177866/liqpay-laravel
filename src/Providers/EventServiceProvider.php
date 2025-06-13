<?php

namespace Alyakin\LiqpayLaravel\Providers;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;
use Alyakin\LiqpayLaravel\Events\LiqpaySubscribed as LiqpaySubscribedEvent;
use Alyakin\LiqpayLaravel\Events\LiqpayUnsubscribed as LiqpayUnsubscribedEvent;
use Alyakin\LiqpayLaravel\Listeners\LiqpaySubscribed as LiqpaySubscribedListener;
use Alyakin\LiqpayLaravel\Listeners\LiqpaySubscriptionPaid;
use Alyakin\LiqpayLaravel\Listeners\LiqpayUnsubscribed as LiqpayUnsubscribedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LiqpaySubscribedEvent::class => [
            LiqpaySubscribedListener::class,
        ],
        LiqpayUnsubscribedEvent::class => [
            LiqpayUnsubscribedListener::class,
        ],
        LiqpayPaymentSucceeded::class => [
            LiqpaySubscriptionPaid::class,
        ],
    ];
}
