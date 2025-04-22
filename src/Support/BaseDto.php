<?php

namespace Alyakin\LiqPayLaravel\Support;

abstract class BaseDto
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        /** @phpstan-ignore-next-line */
        return new static(...$data);
    }

    /**
     * Преобразование DTO в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
