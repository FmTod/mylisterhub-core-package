<?php

namespace MyListerHub\Core\LaravelData\Casts;

use Carbon\CarbonInterval;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class DateIntervalCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): CarbonInterval
    {
        return CarbonInterval::seconds($value);
    }
}
