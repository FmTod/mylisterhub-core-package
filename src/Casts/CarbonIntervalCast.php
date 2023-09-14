<?php

namespace MyListerHub\Core\Casts;

use Carbon\CarbonInterval;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use MyListerHub\Core\Enums\IntervalUnit;

class CarbonIntervalCast implements CastsAttributes
{
    public function __construct(
        protected string $valueColumn,
        protected ?string $unitColumn = null,
        protected bool $updateUnitColumn = true,
        protected ?string $defaultUnit = IntervalUnit::Minutes,
    ) {
    }

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function get($model, string $key, mixed $value, array $attributes): ?CarbonInterval
    {
        return CarbonInterval::make($attributes[$this->valueColumn], $this->unitColumn ? $attributes[$this->unitColumn] : $this->defaultUnit);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function set($model, string $key, mixed $value, array $attributes): array
    {
        if (is_null($value)) {
            return [$this->valueColumn => null];
        }

        if (! $value instanceof CarbonInterval) {
            throw new InvalidArgumentException('The given value is not an CarbonInterval instance.');
        }

        if ($value->totalMinutes === 0) {
            return [$this->valueColumn => null];
        }

        if (! $this->updateUnitColumn) {
            return [
                $this->valueColumn => $value->total($this->unitColumn ? $attributes[$this->unitColumn] : $this->defaultUnit),
            ];
        }

        foreach (IntervalUnit::getOrderedValues(desc: true) as $unit) {
            $total = $value->total($unit);

            if ($total > 0 && is_numeric($total) && floor($total) === (float) $total) {
                return [
                    $this->valueColumn => (int) $total,
                    $this->unitColumn => $unit,
                ];
            }
        }

        return [
            $this->valueColumn => (int) round($value->totalMinutes),
            $this->unitColumn => IntervalUnit::Minutes,
        ];
    }
}
