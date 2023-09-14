<?php

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Http\Resources\Json\JsonResource;

trait HasResource
{
    protected function getResource(): string
    {
        return $this->resource ?? JsonResource::class;
    }

    protected function getCollectionResource(): ?string
    {
        return $this->collectionResource ?? null;
    }
}
