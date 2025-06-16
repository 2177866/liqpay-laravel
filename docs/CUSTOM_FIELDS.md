[&lt; back](../README.md#installation)

### Extending Subscription Model Fields via Event

If you added extra columns to the `liqpay_subscriptions` table (for example, `user_id`),
you can set their values without subclassing any package classes.
Just subscribe to the `LiqpaySubscriptionBeforeSave` event — it fires before the subscription is saved.

**Example: Saving `user_id` from the webhook info JSON:**
in the method `boot` of `app/Providers/AppServiceProvider.php`

```php
use Alyakin\LiqPayLaravel\Events\LiqpaySubscriptionBeforeSave;
use Illuminate\Support\Facades\Event;

Event::listen(LiqpaySubscriptionBeforeSave::class, function (LiqpaySubscriptionBeforeSave $event) {
    // $event->subscription is the Eloquent model to be saved
    // Assume info is a JSON-encoded string with keys like {"user_id":123}
    $info = $event->subscription->info;
    if (is_string($info)) {
        $decoded = json_decode($info, true);
        if (is_array($decoded) && isset($decoded['user_id'])) {
            $event->subscription->user_id = $decoded['user_id'];
        }
    }
});
```

> [!NOTE]
> – The event is triggered only for model saves performed by the package business logic (not for all saves).
> – You can pass and use any context when dispatching the event (for example, the raw request or webhook data).

This approach allows you to extend the storage and logic of subscriptions in a safe, flexible, and upgrade-proof way.
