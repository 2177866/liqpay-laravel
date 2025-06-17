<?php

namespace Alyakin\LiqpayLaravel\Commands;

use Alyakin\LiqpayLaravel\Events\LiqpaySubscriptionBeforeSave;
use Alyakin\LiqpayLaravel\Models\LiqpaySubscription;
use Alyakin\LiqpayLaravel\Services\LiqpayService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SyncSubscriptionsCommand extends Command
{
    protected $signature = 'liqpay:sync-subscriptions
        {--from= : Start date (YYYY-MM-DD)}
        {--to= : End date (YYYY-MM-DD)}
        {--restart : Restart and clear cache}';

    protected $description = 'Sync LiqPay subscriptions archive safely and efficiently';

    private const CACHE_FILE_KEY = 'liqpay:sync:file';

    private const CACHE_INDEX_KEY = 'liqpay:sync:index';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = ''.($this->option('from') ?? config('liqpay.archive_from') ?? now()->subMonth()->toDateString());
        $to = ''.($this->option('to') ?? config('liqpay.archive_to') ?? now()->toDateString());
        $restart = (bool) ($this->option('restart') ?? false);

        [$filePath, $startIndex] = $this->getOrDownloadArchive($from, $to, $restart);

        $processed = $this->processArchive($filePath, $startIndex);

        if (! $processed) {
            return self::FAILURE;
        }

        $this->clearCacheOnFinish();
        $this->info(__('liqpay-laravel::messages.archive_processed'));

        return self::SUCCESS;
    }

    /**
     * Получает путь к архиву и индекс последней успешно обработанной записи.
     *
     * @return array{string, int}
     */
    private function getOrDownloadArchive(string $from, string $to, bool $restart): array
    {
        if ($restart) {
            $this->clearCacheOnFinish();
        }

        $filePath = ''.Cache::get(self::CACHE_FILE_KEY);
        /** @var int $startIndex */
        $startIndex = Cache::get(self::CACHE_INDEX_KEY, 0);

        if ($filePath && ! Storage::exists($filePath)) {
            $this->clearCacheOnFinish();
            $filePath = null;
            $startIndex = 0;
        }

        if (! $filePath) {
            $filePath = $this->downloadArchive($from, $to);
            $startIndex = 0;
            Cache::put(self::CACHE_FILE_KEY, $filePath, $this->getCacheTtl());
            Cache::put(self::CACHE_INDEX_KEY, $startIndex, $this->getCacheTtl());
            $this->info(__('liqpay-laravel::messages.archive_downloaded', ['file' => $filePath]));
        } else {
            $this->info(__('liqpay-laravel::messages.archive_continue', [
                'file' => $filePath,
                'line' => $startIndex,
            ]));
        }

        return [$filePath, $startIndex];
    }

    /**
     * Загружает архив с сервера и сохраняет его локально.
     */
    private function downloadArchive(string $from, string $to): string
    {
        $liqpay = app(LiqpayService::class);
        $response = $liqpay->api('request', [
            'action' => 'reports',
            'date_from' => $from,
            'date_to' => $to,
            'resp_format' => 'json',
        ], true);

        if (! $response || ! is_array($response)) {
            $this->error(__('liqpay-laravel::messages.download_failed'));
            exit();
        }

        if ($response !== null && isset($response['data']['result']) && $response['data']['result'] === 'error') {
            $this->error(__('liqpay-laravel::messages.payment_system_error', [
                'code' => $response['data']['code'] ?? 'unknown',
                'description' => $response['data']['err_description'] ?? 'No description provided',
            ]));
            exit();
        }

        $filename = 'liqpay-archive/'.uniqid('archive_', true).'.json';
        Storage::put($filename, ''.json_encode($response));

        return $filename;
    }

    /**
     * Обрабатывает архив — поэлементно, быстро и экономно по памяти.
     */
    private function processArchive(string $filePath, int $startIndex): bool
    {
        $fullPath = Storage::path($filePath);
        $json = file_get_contents($fullPath);
        if ($json === false) {
            $this->error(__('liqpay-laravel::messages.file_open_failed', ['file' => $fullPath]));

            return false;
        }

        /** @var array{data:mixed, signature:string} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $this->error(__('liqpay-laravel::messages.malformed_archive'));

            return false;
        }

        $payments = $data['data'];
        $count = 0;

        foreach ($payments as $i => $payment) {
            try {
                $this->processPayment($payment);
            } catch (\Throwable $e) {
                $this->error(__('liqpay-laravel::messages.error_at_line', [
                    'line' => $i,
                    'msg' => $e->getMessage(),
                ]));
                $this->setArchiveProgress($i);

                return false;
            }
            $this->setArchiveProgress($i);
            $count++;
        }

        $this->info(__('liqpay-laravel::messages.processed_payments', ['count' => $count]));

        return true;
    }

    /**
     * Основная бизнес-логика обработки записи архива.
     *
     * @param array{
     *  action:?string,
     *  status:?string,
     *  order_id:?string,
     *  amount:?int,
     *  currency:?string,
     *  description:?string,
     *  liqpay_order_id:?string,
     *  payment_id:?string,
     *  create_date:?int,
     *  info:?string
     * } $payment An associative array containing payment details.
     */
    private function processPayment(array $payment): void
    {
        $action = $payment['action'] ?? null;
        $status = $payment['status'] ?? null;
        $orderId = $payment['order_id'] ?? null;

        if (! $orderId || ! in_array($action, ['subscribe', 'regular'], true)) {
            return;
        }

        /** @var LiqpaySubscription $subscription */
        $subscription = LiqpaySubscription::withTrashed()->firstOrNew(
            ['order_id' => $orderId],
            ['amount' => $payment['amount'] ?? null, 'currency' => $payment['currency'] ?? null]
        );
        $save = false;
        $fields = [];

        if ($action === 'subscribe') {
            $fields = $this->mapSubscriptionFields($payment);

            if ($status === 'subscribed') {
                $fields['status'] = 'active';
                $fields['started_at'] = $payment['create_date'] ? $this->tsToDatetime((string) $payment['create_date']) : null;
                $fields['created_at'] = $fields['started_at'];
            } elseif ($status === 'unsubscribed') {
                // This field is not empty and should not be overwritten MANNUALLY.
                $subscription->status = 'inactive';

                $fields['expired_at'] = $payment['create_date'] ? $this->tsToDatetime((string) $payment['create_date']) : null;
            }
            $save = true;
        } elseif ($action === 'regular' && $status === 'success') {
            $fields = $this->mapSubscriptionFields($payment);
            $current = $subscription->last_paid_at;
            $newDate = $payment['create_date'] ? $this->tsToDatetime((string) $payment['create_date']) : null;

            if (! $current || \Carbon\Carbon::parse($newDate)->gt($current)) {
                $fields['last_paid_at'] = $newDate;
                $fields['last_payment_id'] = $payment['payment_id'] ?? null;
            }

            $save = true;
        }

        if ($save) {
            $this->fillIfEmptySafe($subscription, $fields);
            event(new LiqpaySubscriptionBeforeSave($subscription, [
                'payment' => $payment,
            ]));
            $subscription->save();
        }
    }

    /**
     * Маппинг полей для fill().
     *
     * @param  array<string, mixed>  $payment
     * @return array<string, mixed>
     */
    protected function mapSubscriptionFields(array $payment): array
    {
        $fields = collect($payment)->only([
            'description', 'liqpay_order_id', 'payment_id',
        ])->toArray();

        $fields['liqpay_data'] = $payment;
        if (isset($payment['info']) && is_string($payment['info']) && ! empty($payment['info'])) {
            $fields['info'] = $this->tryDecodeInfo($payment['info']) ?? null;
        }

        return $fields;
    }

    /**
     * Заполняет модель, если поля пустые.
     *
     * @param  array<string, mixed>  $data
     */
    protected static function fillIfEmptySafe(Model &$model, array $data): void
    {
        $columns = Schema::getColumnListing($model->getTable());
        $verifiedEmptyColumns = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $columns, true) && empty($model->{$key})) {
                $verifiedEmptyColumns[$key] = $value;
            }
        }
        $model->fill($verifiedEmptyColumns);
    }

    /**
     * Безопасное декодирование info.
     *
     * @return array<string, mixed>|null
     */
    private function tryDecodeInfo(?string $info): ?array
    {
        if (empty($info)) {
            return null;
        }
        $clean = trim($info, " \t\n\r\0\x0B\xEF\xBB\xBF");
        try {
            $decoded = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error(__('liqpay-laravel::messages.json_decode_error', ['error' => $e->getMessage()]));

            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function tsToDatetime(?string $ts): ?string
    {
        if (! $ts) {
            return null;
        }

        // LiqPay timestamp — миллисекунды!
        return \Carbon\Carbon::createFromTimestampMs((int) $ts)->toDateTimeString();
    }

    // === Cache Helpers ===

    private function setArchiveProgress(int $index): void
    {
        Cache::put(self::CACHE_INDEX_KEY, $index, $this->getCacheTtl());
    }

    private function clearCacheOnFinish(): void
    {
        /** @var string $file */
        $file = Cache::pull(self::CACHE_FILE_KEY).'';
        if ($file && Storage::exists($file)) {
            Storage::delete($file);
        }
        Cache::forget(self::CACHE_INDEX_KEY);
    }

    private function getCacheTtl(): int
    {
        /** @var int $ttl */
        $ttl = config('liqpay.cache_ttl', 86400); // Default to 1 day

        return (int) $ttl;
    }
}
