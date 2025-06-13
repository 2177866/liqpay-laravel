<?php

namespace Alyakin\LiqpayLaravel\Http\Requests;

use Alyakin\LiqpayLaravel\DTO\LiqpayWebhookDto;
use Illuminate\Foundation\Http\FormRequest;

class LiqpayWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'string', 'is_base64'],
            'signature' => ['required', 'string', 'liqpay_signature:data'],
        ];
    }

    public function toDto(): LiqpayWebhookDto
    {
        /** @var string $data */
        $data = $this->input('data');

        $json = base64_decode($data);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($json, true);

        return LiqpayWebhookDto::fromArray($decoded);
    }
}
