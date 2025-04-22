<?php

namespace Alyakin\LiqPayLaravel\Http\Requests;

use Alyakin\LiqPayLaravel\DTO\LiqPayWebhookDto;
use Illuminate\Foundation\Http\FormRequest;

class LiqPayWebhookRequest extends FormRequest
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

    public function toDto(): LiqPayWebhookDto
    {
        /** @var string $data */
        $data = $this->input('data');

        $json = base64_decode($data);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($json, true);

        return LiqPayWebhookDto::fromArray($decoded);
    }
}
