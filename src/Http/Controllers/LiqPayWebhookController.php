<?php

namespace Alyakin\LiqPayLaravel\Http\Controllers;

use Alyakin\LiqPayLaravel\Events\LiqpayPaymentFailed;
use Alyakin\LiqPayLaravel\Events\LiqpayPaymentSucceeded;
use Alyakin\LiqPayLaravel\Events\LiqpayPaymentWaiting;
use Alyakin\LiqPayLaravel\Events\LiqpayReversed;
use Alyakin\LiqPayLaravel\Events\LiqpaySubscribed;
use Alyakin\LiqPayLaravel\Events\LiqpayUnsubscribed;
use Alyakin\LiqPayLaravel\Events\LiqpayWebhookReceived;
use Alyakin\LiqPayLaravel\Http\Requests\LiqPayWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class LiqPayWebhookController extends Controller
{
    public function handle(LiqPayWebhookRequest $request): JsonResponse
    {
        $dto = $request->toDto();

        Event::dispatch(new LiqpayWebhookReceived($dto));

        match ($dto->status) {
            'success' => Event::dispatch(new LiqpayPaymentSucceeded($dto)),
            'failure', 'error' => Event::dispatch(new LiqpayPaymentFailed($dto)),
            'subscribed' => Event::dispatch(new LiqpaySubscribed($dto)),
            'unsubscribed' => Event::dispatch(new LiqpayUnsubscribed($dto)),
            'reversed' => Event::dispatch(new LiqpayReversed($dto)),

            'invoice_wait', 'otp_verify', '3ds_verify', 'wait_accept',
            'wait_sender', 'wait_card', 'wait_secure', 'processing' => Event::dispatch(new LiqpayPaymentWaiting($dto)),

            default => null,
        };

        return response()->json(['status' => 'ok']);
    }
}
