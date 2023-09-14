<?php

namespace MyListerHub\Core\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Other()
 * @method static static Facebook()
 * @method static static Instagram()
 * @method static static Twitter()
 * @method static static Pinterest()
 * @method static static LinkedIn()
 */
final class SocialMediaSites extends Enum
{
    public const Other = 0;

    public const Facebook = 1;

    public const Instagram = 2;

    public const Twitter = 3;

    public const Pinterest = 4;

    public const LinkedIn = 5;
}
