<?php

namespace Alyakin\LiqpayLaravel\Support\Traits;

/**
 * Позволяет DTO сохранять неизвестные поля в _attributes и получать к ним доступ через __get
 */
trait HasDynamicAttributes
{
    /**
     * @var array<string, mixed>
     */
    protected array $_attributes = [];

    public function __get(string $key): mixed
    {
        return $this->_attributes[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->_attributes[$key]);
    }

    public static function fromArray(array $data): static
    {
        $ref = new \ReflectionClass(static::class);
        $params = $ref->getConstructor()?->getParameters() ?? [];

        $args = [];
        $knownKeys = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $knownKeys[] = $name;
            $args[$name] = $data[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
        }

        /**@phpstan-ignore-next-line */
        $instance = new static(...$args);
        $instance->_attributes = array_diff_key($data, array_flip($knownKeys));

        return $instance;
    }

    public function toArray(): array
    {
        $base = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($key === '_attributes') {
                continue;
            }
            $base[$key] = $value;
        }

        return array_merge($base, $this->_attributes);
    }
}
