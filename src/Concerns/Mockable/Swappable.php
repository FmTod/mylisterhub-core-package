<?php

namespace MyListerHub\Core\Concerns\Mockable;

trait Swappable
{
    public static function make(...$args): static
    {
        if (static::isSwapped()) {
            return static::getSwappedInstance();
        }

        return new static(...$args);
    }

    public static function swap(...$args): static
    {
        return static::setSwappedInstance(new static(...$args));
    }

    public static function isSwapped(): bool
    {
        return app()->isShared(static::getSwappedInstanceKey());
    }

    public static function clearSwapped(): void
    {
        app()->forgetInstance(static::getSwappedInstanceKey());
    }

    protected static function getSwappedInstanceKey(): string
    {
        return sprintf('swapped:%s', static::class);
    }

    protected static function setSwappedInstance($swap): static
    {
        $instance = app()->instance(static::getSwappedInstanceKey(), $swap);

        app()->bind(static::class, fn () => $instance);

        return $instance;
    }

    protected static function getSwappedInstance(): ?static
    {
        return app(static::getSwappedInstanceKey());
    }
}
