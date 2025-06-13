<?php

namespace Alyakin\LiqpayLaravel\Commands;

use Alyakin\LiqpayLaravel\Models\LiqpaySubscription;
use Alyakin\LiqpayLaravel\Services\LiqpayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SyncSubscriptionsCommand extends Command
{
    protected $signature = 'liqpay:sync-subscriptions
        {--from= : Start date (YYYY-MM-DD)}
        {--to= : End date (YYYY-MM-DD)}
        {--restart : Restart and clear cache}';

    protected $description = 'Sync Liqpay subscriptions archive safely and efficiently';

    private const CACHE_FILE_KEY = 'liqpay:sync:file';

    private const CACHE_LINE_KEY = 'liqpay:sync:line';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $from date in format "YYYY-mm-dd" */
        $from = $this->option('from') ?? config('liqpay.archive_from') ?? now()->subMonth()->toDateString().'';

        /** @var string $to date in format "YYYY-mm-dd" */
        $to = $this->option('to') ?? config('liqpay.archive_to') ?? now()->toDateString().'';

        /** @var bool $restart */
        $restart = $this->option('restart') ?? false;

        [$filePath, $startLine] = $this->getOrDownloadArchive($from, $to, $restart);

        $processed = $this->processArchiveByChunks($filePath, $startLine);
        if (! $processed) {
            return self::FAILURE;
        }

        $this->clearCacheOnFinish();
        $this->info(__('liqpay-laravel::messages.archive_processed'));

        return self::SUCCESS;
    }

    /** Retrieves the archive file path and start line, downloading if necessary.
     *
     * @param  string  $from  Start date in "YYYY-MM-DD" format.
     * @param  string  $to  End date in "YYYY-MM-DD" format.
     * @param  bool  $restart  Whether to clear cache and restart the process.
     * @return array{string, int} Returns an array with file path and start line.
     */
    private function getOrDownloadArchive(string $from, string $to, bool $restart): array
    {
        if ($restart) {
            $this->clearCacheOnFinish();
        }

        /** @var string $filePath */
        $filePath = Cache::get(self::CACHE_FILE_KEY);
        /** @var int $startLine */
        $startLine = Cache::get(self::CACHE_LINE_KEY, 0);

        if ($filePath && ! Storage::exists($filePath)) {
            $this->clearCacheOnFinish();
            $filePath = null;
            $startLine = 0;
        }

        if (! $filePath) {
            $filePath = $this->downloadArchiveCsv($from, $to);
            $startLine = 0;
            Cache::put(self::CACHE_FILE_KEY, $filePath, $this->getCacheTtl());
            Cache::put(self::CACHE_LINE_KEY, $startLine, $this->getCacheTtl());
            $this->info(__('liqpay-laravel::messages.archive_downloaded', ['file' => $filePath]));
        } else {
            $this->info(__('liqpay-laravel::messages.archive_continue', [
                'file' => $filePath,
                'line' => $startLine,
            ]));
        }

        return [$filePath, $startLine];
    }

    private function downloadArchiveCsv(string $from, string $to): string
    {
        /** @var LiqpayService $liqpay */
        $liqpay = app(LiqpayService::class);
        /** @var string $response
         * @throws \RuntimeException
         */
        $response = $liqpay->api('request', [
            'action' => 'payments',
            'date_from' => $from,
            'date_to' => $to,
            'resp_format' => 'csv',
        ], false);

        if (! $response || ! is_string($response)) {
            throw new \RuntimeException(__('liqpay-laravel::messages.download_failed'));
        }

        if (strpos($response, 'action,status,order_id') === false) {
            throw new \RuntimeException(__('liqpay-laravel::messages.download_failed'));
        }

        $filename = 'liqpay-archive/'.uniqid('archive_', true).'.csv';
        Storage::put($filename, $response);

        return $filename;
    }

    private function processArchiveByChunks(string $filePath, int $startLine): bool
    {
        $fullPath = Storage::path($filePath);
        $handle = fopen($fullPath, 'rb');
        if (! $handle) {
            $this->error(__('liqpay-laravel::messages.file_open_failed', ['file' => $fullPath]));

            return false;
        }

        $count = 0;
        $currentLine = 0;
        $header = null;

        // Получаем header (всегда первая строка)
        if (($header = fgetcsv($handle)) === false) {
            $this->error(__('liqpay-laravel::messages.csv_header_missing'));
            fclose($handle);

            return false;
        }
        $currentLine++;

        // Если startLine > 1 — смещаемся на нужную строку
        while ($currentLine < $startLine + 1 && ($row = fgetcsv($handle)) !== false) {
            $currentLine++;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $currentLine++;
            $payment = array_combine($header, $row);
            try {
                $this->processCsvPayment($payment);
            } catch (\Throwable $e) {
                $this->error(__('liqpay-laravel::messages.error_at_line', [
                    'line' => $currentLine,
                    'msg' => $e->getMessage(),
                ]));
                $this->setArchiveProgress($currentLine);
                fclose($handle);

                return false;
            }
            $this->setArchiveProgress($currentLine);
            $count++;
        }
        fclose($handle);

        $this->info(__('liqpay-laravel::messages.processed_payments', ['count' => $count]));

        return true;
    }

    /**
     * @param  array<string, string>  $payment
     */
    private function processCsvPayment(array $payment): void
    {
        $action = $payment['action'] ?? null;
        $status = $payment['status'] ?? null;
        $orderId = $payment['order_id'] ?? null;

        if (! $orderId || ! in_array($action, ['subscribe', 'regular'], true)) {
            return;
        }
        /* @var LiqpaySubscription $subscription */
        $subscription = LiqpaySubscription::withTrashed()->firstOrNew(
            ['order_id' => $orderId],
            ['amount' => $payment['amount'], 'currency' => $payment['currency']]
        );

        if ($action === 'subscribe') {
            if ($status === 'subscribed') {
                $subscription->fill($this->mapSubscriptionFields($payment));
                $subscription->info = $this->tryDecodeInfo($payment['info']) ?? null;
                $subscription->status = 'active';
                $subscription->liqpay_data = $payment;
                $subscription->save();
            } elseif ($status === 'unsubscribed') {
                $subscription->status = 'inactive';
                $subscription->expired_at = $this->tsToDatetime($payment['create_date'] ?? null);
                $subscription->save();
            }
        } elseif ($action === 'regular' && $status === 'success') {
            $current = $subscription->last_paid_at;
            $newDate = $payment['create_date'];
            if (! $current || \Carbon\Carbon::parse($newDate)->gt($current)) {
                $subscription->last_paid_at = $newDate;
                $subscription->last_payment_id = $payment['payment_id'] ?? null;
                $subscription->save();
            }
        }
    }

    /** Maps relevant fields from the payment data to the subscription model.
     *
     * @param  array<string, string>  $payment
     * @return array<string, mixed>
     */
    protected function mapSubscriptionFields(array $payment): array
    {
        return collect($payment)->only([
            'status', 'amount', 'currency', 'description', 'liqpay_order_id', 'payment_id',
        ])->toArray();
    }

    /** Attempts to decode the 'info' field from the payment data.
     *
     * @return array<string, mixed>|null
     */
    private function tryDecodeInfo(?string $info): ?array
    {
        if (empty($info)) {
            return null;
        }
        // Удаляем BOM и невидимые символы
        $clean = trim($info, " \t\n\r\0\x0B\xEF\xBB\xBF");
        try {
            // Пробуем декодировать JSON
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

        return \Carbon\Carbon::createFromTimestampMs((int) $ts)->toDateTimeString();
    }

    // === Cache Helpers ===

    private function setArchiveProgress(int $line): void
    {
        Cache::put(self::CACHE_LINE_KEY, $line, $this->getCacheTtl());
    }

    private function clearCacheOnFinish(): void
    {
        /** @var string $file */
        $file = Cache::pull(self::CACHE_FILE_KEY).'';
        if ($file && Storage::exists($file)) {
            Storage::delete($file);
        }
        Cache::forget(self::CACHE_LINE_KEY);
    }

    private function getCacheTtl(): int
    {
        /** @var int $ttl */
        $ttl = config('liqpay.cache_ttl', 86400);

        return (int) $ttl; // default: 1 day
    }
}
