<?php

namespace MyListerHub\Core\QueryBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;

class LowercaseCoalesceSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';

        $query->orderByRaw("LOWER(COALESCE($property)) $direction");
    }
}
