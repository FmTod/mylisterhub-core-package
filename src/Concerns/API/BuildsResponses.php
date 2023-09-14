<?php

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

trait BuildsResponses
{
    protected function entityResponse(Model $entity): JsonResource
    {
        $resourceClass = $this->getResource();

        /** @var JsonResource $resource */
        $resource = new $resourceClass($entity);

        return $this->addMetaToResource($resource);
    }

    protected function collectionResponse(LengthAwarePaginator|Collection $entities): ResourceCollection
    {
        if ($collectionResourceClass = $this->getCollectionResource()) {
            $collectionResource = new $collectionResourceClass($entities);
        } else {
            /** @var JsonResource $resource */
            $resource = $this->getResource();

            $collectionResource = $resource::collection($entities);
        }

        return $this->addMetaToResource($collectionResource);
    }

    protected function response(Model|LengthAwarePaginator|Collection $response): ResourceCollection|JsonResource
    {
        return $response instanceof Collection || $response instanceof LengthAwarePaginator
            ? $this->collectionResponse($response)
            : $this->entityResponse($response);
    }
}
