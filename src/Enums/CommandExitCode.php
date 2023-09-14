<?php declare(strict_types=1);

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Success()
 * @method static static Failure()
 * @method static static Invalid()
 * @method static static Skipped()
 */
final class CommandExitCode extends Enum
{
    public const Success = 0;
    public const Failure = 1;
    public const Invalid = 2;
    public const Skipped = 3;
}
