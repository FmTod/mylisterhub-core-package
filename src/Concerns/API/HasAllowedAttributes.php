<?php

namespace MyListerHub\Core\Concerns\API;

use Spatie\QueryBuilder\AllowedSort;

trait HasAllowedAttributes
{
    protected function getAllowedFilters(): array
    {
        return $this->allowedFilters ?? $this->getAvailableFields();
    }

    protected function getAllowedSorts(): array
    {
        return $this->allowedSorts ?? $this->getAvailableFields();
    }

    protected function getAllowedFields(): array
    {
        return $this->allowedFields ?? $this->getAvailableFields();
    }

    protected function getAllowedAppends(): array
    {
        return $this->allowedAppends ?? $this->getAvailableAppends();
    }

    protected function getAllowedIncludes(): array
    {
        return $this->allowedIncludes ?? $this->getAvailableIncludes();
    }

    protected function getDefaultSort(): string|array|AllowedSort
    {
        return $this->defaultSort ?? (new $this->model)->getKeyName();
    }
}
