<?php

namespace MyListerHub\Core\Concerns\Mockable;

use Mockery;
use Mockery\MockInterface;

trait Mockable
{
    public static function make(...$args): static|MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        return new static(...$args);
    }

    public static function mock(): MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        $mock = Mockery::mock(static::class);
        $mock->shouldAllowMockingProtectedMethods();

        return static::setFakeResolvedInstance($mock);
    }

    public static function spy(): MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        return static::setFakeResolvedInstance(Mockery::spy(static::class));
    }

    public static function partialMock(): MockInterface
    {
        return static::mock()->makePartial();
    }

    public static function isFake(): bool
    {
        return app()->isShared(static::getFakeResolvedInstanceKey());
    }

    public static function clearFake(): void
    {
        app()->forgetInstance(static::getFakeResolvedInstanceKey());
    }

    protected static function setFakeResolvedInstance(MockInterface $fake): MockInterface
    {
        $instance = app()->instance(static::getFakeResolvedInstanceKey(), $fake);

        app()->bind(static::class, fn () => $instance);

        return $instance;
    }

    protected static function getFakeResolvedInstance(): ?MockInterface
    {
        return app(static::getFakeResolvedInstanceKey());
    }

    protected static function getFakeResolvedInstanceKey(): string
    {
        return sprintf('mock:%s', static::class);
    }
}
