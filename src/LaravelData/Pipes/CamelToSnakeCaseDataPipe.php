<?php

namespace MyListerHub\Core\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Support\DataClass;

class CamelToSnakeCaseDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        return $properties->mapWithKeys(fn ($value, $key) => [Str::snake($key) => $value]);
    }
}
