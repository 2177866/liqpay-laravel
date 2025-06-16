<?php

namespace Alyakin\LiqpayLaravel\Services;

use Alyakin\LiqpayLaravel\Contracts\LiqpayServiceInterface;
use Alyakin\LiqpayLaravel\DTO\LiqpayRequestDto;
use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;
use Alyakin\LiqpayLaravel\Helpers\LiqpaySignatureValidator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiqpayService implements LiqpayServiceInterface
{
    protected string $apiUrl = 'https://www.liqpay.ua/api/';

    protected string $apiVersion = '3';

    protected string $publicKey;

    protected string $privateKey;

    /**
     * @var array<string> Список поддерживаемых валют
     */
    protected array $supportedCurrencies = ['USD', 'UAH', 'EUR'];

    protected string $currencyDefault = 'UAH';

    public function __construct()
    {
        // Ensure the required configuration is set
        if (! Config::has('liqpay.public_key') || ! Config::has('liqpay.private_key')) {
            throw new \RuntimeException(__('liqpay-laravel::messages.config_missing'));
        }
        $this->publicKey = Config::get('liqpay.public_key').'';
        $this->privateKey = Config::get('liqpay.private_key').'';

        $this->apiUrl = Config::get('liqpay.checkout_url', $this->apiUrl).'';
        if (! filter_var($this->apiUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(__('liqpay-laravel::messages.invalid_url'));
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, string>
     */
    public function prepareRequest(array $params): array
    {
        $params['version'] = $params['version'] ?? $this->apiVersion;
        $params['public_key'] = $this->publicKey;

        $json = json_encode($params, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \UnexpectedValueException(__('liqpay-laravel::messages.json_encode_failed', [
                'error' => json_last_error_msg(),
            ]));
        }

        $data = base64_encode($json);
        $signature = LiqpaySignatureValidator::generate($data, $this->privateKey);

        return [
            'data' => $data,
            'signature' => $signature,
        ];
    }

    public function getPaymentUrl(LiqpayRequestDto $dto): string
    {
        ['data' => $data, 'signature' => $signature] = $this->prepareRequest($dto->toArray());

        return "{$this->apiUrl}{$this->apiVersion}/checkout?data={$data}&signature={$signature}";
    }

    public function decodeWebhook(string $data, string $signature): LiqpayWebhookDto
    {
        if (! LiqpaySignatureValidator::verify($data, $signature, $this->privateKey)) {
            throw new \UnexpectedValueException(__('liqpay-laravel::messages.invalid_signature'));
        }

        $decoded = json_decode(base64_decode($data), true);

        if (! is_array($decoded)) {
            throw new \UnexpectedValueException(__('liqpay-laravel::messages.json_decode_error', [
                'error' => json_last_error_msg(),
            ]));
        }

        /** @var array<string,mixed> $decoded */
        return LiqpayWebhookDto::fromArray($decoded);
    }

    /**
     * Makes a request to the Liqpay API.
     *
     * @param  string  $path  The API endpoint path.
     * @param  array<string, mixed>  $params  The parameters to send in the request.
     * @return array<string, mixed>|string The decoded JSON response from the API.
     *
     * @throws \UnexpectedValueException If JSON encoding fails or response is invalid.
     * @throws \RuntimeException If the API request fails.
     */
    public function api(string $path, array $params, bool $is_json = true): array|string
    {
        $request = $this->prepareRequest($params);

        $response = Http::asForm()->post("{$this->apiUrl}{$path}", $request);

        if ($response->failed()) {
            Log::error(__('liqpay-laravel::messages.liqpay_api_error', ['response' => $response->status()]), [
                'path' => "{$this->apiUrl}{$path}",
                'params' => $params,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(__('liqpay-laravel::messages.liqpay_api_error', [
                'response' => $response->body(),
            ]));
        }

        if ($is_json) {
            $decoded = $response->json();
            if (! is_array($decoded)) {
                throw new \UnexpectedValueException(__('liqpay-laravel::messages.invalid_json_response'));
            }

            return $decoded;
        }

        return $response->body();
    }

    /**
     * Деактивация подписки в Liqpay по order_id.
     *
     * @return array<string, mixed>
     */
    public function unsubscribe(string $orderId): array
    {
        $requestRaw = [
            'action' => 'unsubscribe',
            'order_id' => $orderId,
        ];

        /** @var array<string, mixed> $response */
        $response = $this->api('request', $requestRaw, true);
        $this->handleAPIErrorResponse($requestRaw, $response);

        return $response;
    }

    /**
     * Обновление параметров подписки через Liqpay.
     *
     * @return array<string, mixed>
     */
    public function subscribeUpdate(string $orderId, ?string $currency = null, ?string $description = null): array
    {
        // $currency can only USD, UAH, EUR
        if ($currency && ! in_array($currency, $this->supportedCurrencies, true)) {
            $currency = $this->currencyDefault;
        }

        $requestRaw = [
            'action' => 'subscribe_update',
            'order_id' => $orderId,
            'currency' => $currency,
            'description' => $description ?? '',
        ];

        /** @var array<string, mixed> $response */
        $response = $this->api('request', $requestRaw, true);

        $this->handleAPIErrorResponse($requestRaw, $response);

        return $response;
    }

    /**
     * Обрабатывает ошибочный ответ API.
     *
     * @param  array<string, mixed>  $request  Данные запроса
     * @param  array<string, mixed>  $response  Данные ответа
     */
    public function handleAPIErrorResponse(array $request, array $response): bool
    {
        if (! isset($response['result']) || $response['result'] !== 'ok') {
            Log::error(__('liqpay-laravel::messages.api_error'), [
                'request' => $request,
                'response' => $response,
            ]);

            return false;
        }

        return true;
    }
}
