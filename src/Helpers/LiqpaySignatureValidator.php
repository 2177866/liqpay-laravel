<?php

namespace Alyakin\LiqpayLaravel\Helpers;

class LiqpaySignatureValidator
{
    public static function generate(string $data, string $privateKey): string
    {
        return base64_encode(sha1($privateKey.$data.$privateKey, true));
    }

    public static function verify(string $data, string $signature, string $privateKey): bool
    {
        return hash_equals(self::generate($data, $privateKey), $signature);
    }
}
