<?php

namespace MyListerHub\Core\Tabulator;

use FmTod\LaravelTabulator\TabulatorTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

abstract class AbstractTable extends TabulatorTable
{
    public function json(): LengthAwarePaginator|Arrayable|Jsonable|array
    {
        if (! $this->options('pagination', false) || $this->options('paginationMode') !== 'remote') {
            return parent::json();
        }

        /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query */
        $query = $this->getScopedQuery();
        $pageSize = $this->request->input('size');

        $data = $query->fastPaginate($pageSize)->toArray();
        $data['last_row'] = $data['total'];

        return $data;
    }
}
