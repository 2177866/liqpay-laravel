<?php

namespace Alyakin\LiqpayLaravel\Listeners;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;
use Alyakin\LiqpayLaravel\Models\LiqpaySubscription;
use Carbon\Carbon;

class LiqpaySubscriptionPaid
{
    public function handle(LiqpayPaymentSucceeded $event): void
    {
        $dto = $event->dto;

        if (($dto->action ?? null) !== 'regular') {
            // Только регулярные платежи по подписке
            return;
        }

        $subscription = LiqpaySubscription::where('order_id', $dto->order_id)->first();
        if (! $subscription) {
            return;
        }

        // Если это новый (более свежий) платёж — обновляем
        $newPaidAt = isset($dto->create_date) ? Carbon::createFromTimestampMs((int) $dto->create_date) : null;
        if ($newPaidAt && (
            ! $subscription->last_paid_at ||
            Carbon::parse($subscription->last_paid_at)->lt($newPaidAt)
        )) {
            $subscription->last_paid_at = $newPaidAt;
            $subscription->last_payment_id = $dto->payment_id ? (string) $dto->payment_id : null;
            $subscription->save();
        }
    }
}
