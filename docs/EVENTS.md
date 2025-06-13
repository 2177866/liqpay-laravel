[&lt; back](../README.md#handling-webhook-from-liqpay-events)

### Important: Error Handling in Custom Listeners

> [!CAUTION]
>
> If you are adding your own listeners to the package events (`LiqpaySubscribed`, `LiqpayUnsubscribed`, etc.), make sure they do not throw unhandled exceptions.
>
> In Laravel (and in this package), if even one listener throws an error or exception — THE ENTIRE event handling chain stops.
> Other listeners will not be called, and the LiqPay Webhook controller will return a 500 error.
>
> **This is the standard behavior of the Laravel Event Dispatcher** and applies to all events, including those of the package.

#### How to Do It Right:

- Always use try/catch inside your custom listener if there is a possibility of failure:
  ```php
  public function handle($event): void
  {
    try {
      // Your logic
    } catch (\Throwable $e) {
      \Log::error('Error in custom listener: '.$e->getMessage(), ['event' => $event]);
      // ...graceful fail logic...
    }
  }
  ```
- Ensure that your listeners do not lead to a 500 error in production.
- If a listener depends on external services, always implement fallback and logging.

#### Example of Consequences of an Unhandled Exception:

```php
Event::listen(LiqpaySubscribed::class, function ($event) {
    throw new \RuntimeException('Error!');
});
```

In this case, no other listener for the `LiqpaySubscribed` event will be called,
and the LiqPay webhook will receive a 500 error and may attempt to retry the request.

---

**We recommend thoroughly testing your custom listeners for fault tolerance!**
