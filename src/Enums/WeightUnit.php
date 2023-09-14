<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Pounds()
 * @method static static Kilograms()
 * @method static static Ounces()
 */
final class WeightUnit extends Enum
{
    public const Pounds = 'lb';

    public const Kilograms = 'kg';

    public const Ounces = 'oz';

    public static function parse(string $value): WeightUnit
    {
        return match (strtolower($value)) {
            'lb', 'lbs', 'pound', 'pounds' => self::Pounds(),
            'kg', 'kgs', 'kilogram', 'kilograms' => self::Kilograms(),
            'oz', 'ounces', 'ounce' => self::Ounces(),
            default => throw new \Exception('Invalid weight unit'),
        };
    }
}
