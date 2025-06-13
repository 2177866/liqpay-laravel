[< back](../README.md#localization--translations)
## Best Practices for Translations (i18n)

### Why Use Namespaced Translation Keys

- All user-facing messages in this package are translation-ready.
- Keys are always namespaced as `liqpay-laravel::messages.KEY` to avoid conflicts and enable easy overrides.

### How to Add or Override Translations

1. **Publish translation files** to your app (recommended for customization):

   ```shell
   php artisan vendor:publish --tag=liqpay-laravel-lang
   # or, if tag is not defined, publish manually:
   php artisan vendor:publish --provider="Alyakin\LiqpayLaravel\LiqpayServiceProvider" --tag=liqpay-lang
   ```

   Published files will appear in:
   `resources/lang/vendor/liqpay-laravel/{locale}/messages.php`

2. **Update or extend keys** as needed for your project:

   ```php
   // resources/lang/vendor/liqpay-laravel/ru/messages.php
   return [
       'archive_processed' => 'Архив успешно обработан!',
       // Add or customize more...
   ];
   ```

3. **Never use raw messages as keys** in your code.
   Always reference translation keys:

   ```php
   __('liqpay-laravel::messages.archive_processed');
   ```

### Default Languages

- The package provides English (`en`), Russian (`ru`), and Ukrainian (`uk`) translations out of the box.
- To add other languages, copy the `messages.php` file to your desired locale folder.

### Adding New Messages

- When adding new output in your command or package,
  always add the key to all three language files (`en`, `ru`, `uk`) with the appropriate translation.

---

## Example: Using Translations in Your Code

```php
$this->info(__('liqpay-laravel::messages.archive_processed'));

// With parameters
$this->info(__('liqpay-laravel::messages.archive_downloaded', ['file' => $filePath]));
```

---

## Advanced: Overriding Package Translations Globally

- If you want to override **all** package translations,
  publish the lang files as above and update the content in `resources/lang/vendor/liqpay-laravel/{locale}/messages.php`.
- Laravel will use your app's translations in priority over the vendor's.

---

## ⚡️ Summary

- **Always use namespaced keys:** `liqpay-laravel::messages.KEY`
- **Publish lang files to customize.**
- **Keep translations up to date for every locale you use.**
