<?php

namespace MyListerHub\Core\Concerns\API;

trait HasAvailableAttributes
{
    private array $availableFields = [];

    private array $availableAppends = [];

    private array $availableIncludes = [];

    protected function getAvailableFields(): array
    {
        if (empty($this->availableFields) && is_callable([$this->model, 'getAttributeNames'])) {
            $this->availableFields = $this->model::getAttributeNames();
        }

        return $this->availableFields;
    }

    protected function getAvailableAppends(): array
    {
        if (empty($this->availableAppends) && is_callable([$this->model, 'getAppendNames'])) {
            $this->availableAppends = $this->model::getAppendNames();
        }

        return $this->availableAppends;
    }

    protected function getAvailableIncludes(): array
    {
        if (empty($this->availableIncludes) && is_callable([$this->model, 'getRelationNames'])) {
            $this->availableIncludes = $this->model::getRelationNames();
        }

        return $this->availableIncludes;
    }
}
