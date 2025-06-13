<?php

namespace Alyakin\LiqpayLaravel\Http\Controllers;

use Alyakin\LiqpayLaravel\Events\LiqpayPaymentFailed;
use Alyakin\LiqpayLaravel\Events\LiqpayPaymentSucceeded;
use Alyakin\LiqpayLaravel\Events\LiqpayPaymentWaiting;
use Alyakin\LiqpayLaravel\Events\LiqpayReversed;
use Alyakin\LiqpayLaravel\Events\LiqpaySubscribed;
use Alyakin\LiqpayLaravel\Events\LiqpayUnsubscribed;
use Alyakin\LiqpayLaravel\Events\LiqpayWebhookReceived;
use Alyakin\LiqpayLaravel\Http\Requests\LiqpayWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class LiqpayWebhookController extends Controller
{
    public function handle(LiqpayWebhookRequest $request): JsonResponse
    {
        $dto = $request->toDto();

        Event::dispatch(new LiqpayWebhookReceived($dto));

        match ($dto->status) {
            'success' => Event::dispatch(new LiqpayPaymentSucceeded($dto)),
            'subscribed' => Event::dispatch(new LiqpaySubscribed($dto)),
            'unsubscribed' => Event::dispatch(new LiqpayUnsubscribed($dto)),
            'reversed' => Event::dispatch(new LiqpayReversed($dto)),

            'failure', 'error' => Event::dispatch(new LiqpayPaymentFailed($dto)),

            'invoice_wait', 'otp_verify', '3ds_verify', 'wait_accept',
            'wait_sender', 'wait_card', 'wait_secure', 'processing' => Event::dispatch(new LiqpayPaymentWaiting($dto)),

            default => null,
        };

        return response()->json(['status' => 'ok']);
    }
}
