<?php

namespace MyListerHub\Core\Concerns\Models;

use Illuminate\Database\Eloquent\Builder;

trait HasExternalKey
{
    public function getExternalKeyName(): string
    {
        return 'external_key';
    }

    public function getExternalKey()
    {
        return $this->getAttribute($this->getExternalKeyName());
    }

    public function scopeWhereExternalKey(Builder $query, mixed $value): Builder
    {
        return $query->where($this->getExternalKeyName(), $value);
    }

    public function scopeWhereKey(Builder $query, mixed $value): Builder
    {
        return $query->where(function (Builder $query) use ($value) {
            $query->where($this->getKeyName(), $value)
                ->orWhere($this->getExternalKeyName(), $value);
        });
    }
}
