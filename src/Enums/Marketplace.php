<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static eBay()
 * @method static static Amazon()
 * @method static static Sears()
 * @method static static NewEgg()
 * @method static static UnbeatableSale()
 * @method static static Walmart()
 * @method static static Direct()
 * @method static static Other()
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 * @method static static fromValue($enumValue)
 */
#[TypeScript]
final class Marketplace extends Enum
{
    public const eBay = 'eBay';

    public const Amazon = 'Amazon';

    public const Sears = 'Sears';

    public const NewEgg = 'NewEgg';

    public const UnbeatableSale = 'Unbeatable Sale';

    public const Walmart = 'Walmart';

    public const Direct = 'Direct';

    public const Other = 'Other';
}
