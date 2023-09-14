<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static Minutes()
 * @method static static Hours()
 * @method static static Days()
 * @method static static Weeks()
 * @method static static Months()
 * @method static static Years()
 */
#[TypeScript]
final class IntervalUnit extends Enum
{
    public const Minutes = 'minutes';

    public const Hours = 'hours';

    public const Days = 'days';

    public const Weeks = 'weeks';

    public const Months = 'months';

    public const Years = 'years';

    public static function getOrderedValues(bool $desc = false): array
    {
        $order = [
            self::Minutes,
            self::Hours,
            self::Days,
            self::Weeks,
            self::Months,
            self::Years,
        ];

        return $desc ? array_reverse($order) : $order;
    }
}
