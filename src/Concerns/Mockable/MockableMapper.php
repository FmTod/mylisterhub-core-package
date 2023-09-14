<?php

namespace MyListerHub\Core\Concerns\Mockable;

use BadMethodCallException;
use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\MockInterface;
use MyListerHub\Core\Concerns\Mockable\Mockable;

trait MockableMapper
{
    use Mockable;

    /**
     * @throws \BadMethodCallException
     */
    protected static function getFromMethod(string $from): string
    {
        $method = "from$from";

        throw_unless(method_exists(static::class, $method), new BadMethodCallException("Method [$method] does not exist."));

        return $method;
    }

    public static function shouldRun(string $from, mixed $return = null): Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
    {
        return when(
            $return,
            static::mock()->allows(static::getFromMethod($from)),
            fn (Expectation|ExpectationInterface|HigherOrderMessage|MockInterface $mock) => $mock->andReturn($return),
        );
    }

    public static function shouldNotRun(string $from): HigherOrderMessage|Expectation|ExpectationInterface
    {
        return static::mock()
            ->allows(static::getFromMethod($from))
            ->never();
    }

    public static function allowToRun(string $from): HigherOrderMessage|Expectation|MockInterface|ExpectationInterface
    {
        return static::spy()->allows(static::getFromMethod($from));
    }
}
