<?php

namespace App\DTO;

abstract readonly class BaseDTO implements \JsonSerializable
{
    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            $result[$key] = $this->normalizeValue($value);
        }

        return $result;
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(
                fn ($item) => $this->normalizeValue($item),
                $value
            );
        }

        return $value;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
