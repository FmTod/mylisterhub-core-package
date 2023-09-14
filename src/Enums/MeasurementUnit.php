<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Inches()
 * @method static static Feet()
 * @method static static Millimeters()
 * @method static static Centimeters()
 */
final class MeasurementUnit extends Enum
{
    public const Inches = 'in';

    public const Feet = 'ft';

    public const Millimeters = 'mm';

    public const Centimeters = 'cm';

    public static function parse(string $value): MeasurementUnit
    {
        return match (strtolower($value)) {
            'in', 'inch', 'inches' => self::Inches(),
            'ft', 'foot', 'feet', 'ft.' => self::Feet(),
            'mm', 'millimeter', 'millimeters' => self::Millimeters(),
            'cm', 'centimeter', 'centimeters' => self::Centimeters(),
            default => throw new \Exception('Invalid measurement unit'),
        };
    }
}
