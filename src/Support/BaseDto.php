<?php

namespace Alyakin\LiqpayLaravel\Support;

abstract class BaseDto
{
    /**
     * @var array<string, string[]>
     */
    protected static array $constructorParamsCache = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        /** @phpstan-ignore-next-line */
        return new static(...$data);
    }

    public static function fromObject(object $dto): static
    {
        // Преобразуем DTO в массив
        $source = method_exists($dto, 'toArray')
            ? $dto->toArray()
            : get_object_vars($dto);

        $class = static::class;

        if (! isset(self::$constructorParamsCache[$class])) {
            $reflection = new \ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            if (! $constructor) {
                throw new \RuntimeException("DTO {$class} must have a constructor.");
            }

            self::$constructorParamsCache[$class] = array_map(
                fn (\ReflectionParameter $param) => $param->getName(),
                $constructor->getParameters()
            );
        }

        $allowedKeys = self::$constructorParamsCache[$class];

        // Оставляем только допустимые ключи
        $filtered = array_intersect_key($source, array_flip($allowedKeys));

        /** @phpstan-ignore-next-line */
        return new static(...$filtered);
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
