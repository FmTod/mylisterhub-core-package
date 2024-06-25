<?php

namespace MyListerHub\Core\Concerns\Enums;

use Illuminate\Support\Str;

trait CaseInsensitiveValue
{
    public static function fromValue(mixed $enumValue): static
    {
        foreach (self::getInstances() as $enum) {
            if (strtolower($enum->value) === strtolower($enumValue)) {
                return $enum;
            }
        }

        return parent::fromValue($enumValue);
    }
}
