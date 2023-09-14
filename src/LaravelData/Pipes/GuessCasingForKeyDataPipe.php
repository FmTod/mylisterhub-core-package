<?php

namespace MyListerHub\Core\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Support\DataClass;

class GuessCasingForKeyDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        return $properties->mapWithKeys(function ($value, $key) use ($class) {
            if ($class->properties->has($key)) {
                return [$key => $value];
            }

            if ($class->properties->has(Str::camel($key))) {
                return [Str::camel($key) => $value];
            }

            if ($class->properties->has(Str::snake($key))) {
                return [Str::snake($key) => $value];
            }

            if ($class->properties->has(Str::kebab($key))) {
                return [Str::kebab($key) => $value];
            }

            return [$key => $value];
        });
    }
}
