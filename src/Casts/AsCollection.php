<?php

namespace MyListerHub\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class AsCollection extends \Illuminate\Database\Eloquent\Casts\AsCollection
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        $forceCollection = isset($arguments[0]) && $arguments[0];

        if (! $forceCollection) {
            return parent::castUsing($arguments);
        }

        return new class implements CastsAttributes
        {
            /**
             * Transform the attribute from the underlying model values.
             *
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  mixed  $value
             */
            public function get($model, string $key, $value, array $attributes): ?Collection
            {
                if (empty($value)) {
                    return null;
                }

                return new Collection(isset($attributes[$key]) ? json_decode($attributes[$key], true, 512, JSON_THROW_ON_ERROR) : []);
            }

            /**
             * Transform the attribute to its underlying model values.
             *
             * @param  \Illuminate\Database\Eloquent\Model  $model
             * @param  mixed  $value
             * @return mixed
             */
            public function set($model, string $key, $value, array $attributes): array
            {
                return [$key => json_encode($value, JSON_THROW_ON_ERROR)];
            }
        };
    }
}
