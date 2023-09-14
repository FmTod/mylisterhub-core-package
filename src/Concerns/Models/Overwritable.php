<?php

namespace MyListerHub\Core\Concerns\Models;

trait Overwritable
{
    public function overwrite(array $attributes = [], array $options = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        $attributes = array_merge(array_fill_keys(self::getAttributeNames(), null), $this->attributes, $attributes);

        return $this->fill($attributes)->save($options);
    }
}
