<?php

namespace Alyakin\LiqpayLaravel\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpaySubscriptionBeforeSave;
use Alyakin\LiqpayLaravel\Events\LiqpayUnsubscribed as LiqpayUnsubscribedEvent;
use Alyakin\LiqpayLaravel\Models\LiqpaySubscription;

class LiqpayUnsubscribed
{
    public function handle(LiqpayUnsubscribedEvent $event): void
    {
        /** @var \Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto $dto */
        $dto = $event->dto;

        $subscription = LiqpaySubscription::where('order_id', $dto->order_id)->first();

        if ($subscription) {
            $subscription->fill([
                'status' => 'inactive',
                'expired_at' => now(),
            ]);

            // Trigger the event before saving the subscription
            event(new LiqpaySubscriptionBeforeSave($subscription, [
                'payment' => $dto,
            ]));

            $subscription->save();
        }
    }
}
