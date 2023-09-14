<?php

namespace MyListerHub\Core\TypeScript;

use Based\TypeScript\Definitions\TypeScriptType as BaseType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use ReflectionFunction;
use ReflectionUnionType;

class TypeScriptType extends BaseType
{
    public static function fromFunction(ReflectionFunction $method): array
    {
        $types = $method->getReturnType() instanceof ReflectionUnionType
            ? $method->getReturnType()->getTypes()
            : (string) $method->getReturnType();

        if (is_string($types) && Str::contains($types, '?')) {
            $types = [
                str_replace('?', '', $types),
                self::NULL,
            ];
        }

        return collect($types)
            ->map(function (string $type) {
                return match ($type) {
                    'int', 'float' => self::NUMBER,
                    'string' => self::STRING,
                    'array' => self::array(),
                    'null' => self::NULL,
                    'bool' => self::BOOLEAN,
                    default => self::ANY,
                };
            })
            ->toArray();
    }

    public static function fromAttribute(Attribute $attribute): array
    {
        if (! $attribute->get) {
            return [self::ANY];
        }

        return static::fromFunction(new ReflectionFunction($attribute->get));
    }
}
