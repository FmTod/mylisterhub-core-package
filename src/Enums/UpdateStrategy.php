<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Overwrite()
 * @method static static Complete()
 * @method static static Update()
 * @method static static Skip()
 * @method static static fromValue($enumValue)
 */
final class UpdateStrategy extends Enum
{
    public const Overwrite = 'Overwrite';

    public const Complete = 'Complete';

    public const Update = 'Update';

    public const Skip = 'Skip';
}
