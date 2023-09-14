<?php

namespace MyListerHub\Core\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class BenSampoEnumTransformer implements Transformer
{
    /**
     * @param  \BenSampo\Enum\Enum  $value
     * @return string|int
     */
    public function transform(DataProperty $property, mixed $value): mixed
    {
        return $value->value;
    }
}
