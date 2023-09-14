<?php

namespace MyListerHub\Core\Dto;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

/**
 * @template TModel
 */
class DuplicateData extends Data
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model|TModel  $original
     * @param  \Illuminate\Database\Eloquent\Collection<TModel>  $duplicates
     */
    public function __construct(
        public Model $original,
        public Collection $duplicates,
    ) {
    }

    public function toArray(): array
    {
        return [
            'original' => $this->original->toArray(),
            'duplicates' => $this->duplicates->toArray(),
        ];
    }
}
