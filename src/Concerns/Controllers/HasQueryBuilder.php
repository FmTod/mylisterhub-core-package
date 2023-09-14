<?php

namespace MyListerHub\Core\Concerns\Controllers;

use Illuminate\Database\Eloquent\Builder;
use MyListerHub\Core\QueryBuilder\QueryBuilder;

trait HasQueryBuilder
{
    /**
     * Get query builder for the launching profile model.
     */
    protected function queryBuilder(string $className, array $includes = [], array $filters = null, array $appends = [], array $attributes = null, string $defaultSort = '-created_at'): QueryBuilder|Builder
    {
        if (! isset($attributes)) {
            $attributes = is_callable([$className, 'getAttributeNames'])
                ? array_merge($className::getAttributeNames(), ['*'])
                : [];
        }

        if (! isset($filters)) {
            $filters = $attributes;
        }

        return QueryBuilder::for($className)
            ->allowedFields($attributes)
            ->allowedFilters($filters)
            ->allowedSorts($attributes)
            ->defaultSort($defaultSort)
            ->allowedAppends($appends)
            ->allowedIncludes($includes);
    }
}
