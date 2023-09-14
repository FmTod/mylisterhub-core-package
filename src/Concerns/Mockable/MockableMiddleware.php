<?php

namespace MyListerHub\Core\Concerns\Mockable;

use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\MockInterface;

trait MockableMiddleware
{
    use Mockable;

    public static function shouldAllowJobs(): Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
    {
        return static::partialMock()
            ->allows('handle')
            ->andReturnUsing(fn (mixed $job, callable $next) => $next($job));
    }

    public static function shouldFilterJobs(): Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
    {
        return static::partialMock()
            ->allows('handle')
            ->andReturnFalse();
    }

    public static function shouldRun(): Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
    {
        return static::mock()->allows('handle');
    }

    public static function shouldNotRun(): HigherOrderMessage|Expectation|ExpectationInterface
    {
        return static::mock()
            ->allows('handle')
            ->never();
    }

    public static function allowToRun(): HigherOrderMessage|Expectation|MockInterface|ExpectationInterface
    {
        return static::spy()->allows('handle');
    }
}
