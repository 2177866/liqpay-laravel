<?php

namespace Alyakin\LiqpayLaravel\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpaySubscribed as LiqpaySubscribedEvent;
use Alyakin\LiqpayLaravel\Models\LiqpaySubscription;

class LiqpaySubscribed
{
    public function handle(LiqpaySubscribedEvent $event): void
    {
        /* @var \Alyakin\LiqpayLaravel\DTOs\LiqpaySubscribedDTO $dto */
        $dto = $event->dto;
        $info = $dto->info ? json_decode($dto->info, true) : null;
        LiqpaySubscription::updateOrCreate(
            ['order_id' => $dto->order_id],
            [
                'amount' => (float) $dto->amount,
                'currency' => $dto->currency,
                'status' => 'active',
                'started_at' => now(),
                'liqpay_data' => $dto->toArray(),
                'info' => $info,
            ]
        );
    }
}
