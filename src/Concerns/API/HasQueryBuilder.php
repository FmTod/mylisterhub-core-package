<?php

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Database\Eloquent\Builder;
use MyListerHub\Core\QueryBuilder\QueryBuilder;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
trait HasQueryBuilder
{
    /** @return class-string<T> */
    protected function getModel(): string
    {
        return $this->model;
    }

    protected function getAlwaysInclude(): array
    {
        return $this->alwaysInclude ?? [];
    }

    /** @return \App\Services\QueryBuilder\QueryBuilder|T */
    protected function query(): QueryBuilder|Builder
    {
        return QueryBuilder::for($this->getModel())
            ->allowedFields($this->getAllowedFields())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedAppends($this->getAllowedAppends())
            ->allowedIncludes($this->getAllowedIncludes())
            ->defaultSort($this->getDefaultSort())
            ->with($this->getAlwaysInclude());
    }
}
