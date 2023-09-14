<?php

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait HasMeta
{
    protected function addMetaToResource(ResourceCollection|JsonResource $resource): ResourceCollection|JsonResource
    {
        if (isset($this->meta) && count($this->meta)) {
            $resource->additional(['meta' => $this->meta]);
        }

        return $resource;
    }
}
