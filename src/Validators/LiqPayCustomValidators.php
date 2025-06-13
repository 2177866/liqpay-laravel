<?php

namespace Alyakin\LiqpayLaravel\Validators;

use Alyakin\LiqpayLaravel\Helpers\LiqpaySignatureValidator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class LiqpayCustomValidators
{
    public static function register(): void
    {
        Validator::extend('is_base64', function ($attribute, $value) {
            $decoded = base64_decode($value, true);
            if (! is_string($decoded)) {
                return false;
            }

            return base64_encode($decoded) === $value;
        }, 'Поле :attribute должно быть корректной base64 строкой.');

        Validator::extend('liqpay_signature', function ($attribute, $value, $parameters, $validator) {
            $dataField = $parameters[0] ?? null;

            $data = $validator->getData()[$dataField] ?? null;
            if (! is_string($data) || ! is_string($value)) {
                return false;
            }
            /** @var string $privateKey */
            $privateKey = Config::get('liqpay.private_key');

            return LiqpaySignatureValidator::verify($data, $value, $privateKey);
        }, 'Неверная подпись Liqpay.');
    }
}
