<?php

namespace MyListerHub\Core\Concerns\Enums;

use Illuminate\Support\Str;

trait Parseable
{
    public static function parse(mixed $value): self
    {
        foreach (self::getInstances() as $enum) {
            if (Str::containsAll($value, preg_split('/[\-_ ]/', $enum->value), true)) {
                return $enum;
            }
        }

        return self::fromValue($value);
    }

    public static function tryParse(mixed $value): ?self
    {
        try {
            return self::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
