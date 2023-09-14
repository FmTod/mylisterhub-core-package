<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Points()
 * @method static static Pixels()
 * @method static static Em()
 * @method static static Rem()
 */
final class FontUnits extends Enum
{
    public const Points = 'pt';

    public const Pixels = 'px';

    public const Em = 'em';

    public const Rem = 'rem';
}
