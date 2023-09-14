<?php

namespace MyListerHub\Core\Concerns\Tabulator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasRelationCount
{
    protected function filterRelationCount(Builder $query, string $field, string $type, array|string $value): Builder
    {
        if ($type !== 'minMax') {
            return $query;
        }

        $field = str_replace('_count', '', $field);

        if ($query->getModel()->isRelation(Str::camel($field))) {
            $field = Str::camel($field);
        }

        if (is_array($value) && isset($value['min'])) {
            $query->has($field, '>=', $value['min']);
        }

        if (is_array($value) && isset($value['max'])) {
            $query->has($field, '<=', $value['max']);
        }

        return $query;
    }
}
