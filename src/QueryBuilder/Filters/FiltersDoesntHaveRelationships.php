<?php

namespace MyListerHub\Core\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersDoesntHaveRelationships implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $columns = is_array($value) ? $value : explode(',', $value);
        array_walk($columns, static fn ($column) => $query->doesntHave($column));
    }
}
