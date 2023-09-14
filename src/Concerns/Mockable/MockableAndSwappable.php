<?php

namespace MyListerHub\Core\Concerns\Mockable;

trait MockableAndSwappable
{
    use Mockable {
        Mockable::make as mockMake;
    }

    use Swappable {
        Swappable::make as swapMake;
    }

    public static function make(...$args): static
    {
        if (static::isSwapped()) {
            return static::swapMake(...$args);
        }

        return static::mockMake(...$args);
    }
}
