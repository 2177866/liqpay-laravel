<?php

namespace Alyakin\LiqpayLaravel\Events;

class LiqpaySubscriptionBeforeSave
{
    public function __construct(
        public \Alyakin\LiqpayLaravel\Models\LiqpaySubscription $subscription,
        public mixed $context = null) {}
}
