<?php

namespace MyListerHub\Core\QueryBuilder;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MyListerHub\Core\Concerns\QueryBuilder\AppendsAttributesToResults;
use Spatie\QueryBuilder\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    use AppendsAttributesToResults;

    /**
     * @return \MyListerHub\Core\QueryBuilder\QueryBuilder|\Illuminate\Contracts\Pagination\CursorPaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|mixed
     */
    public function __call($name, $arguments)
    {
        $result = parent::__call($name, $arguments);

        if ($result instanceof Model) {
            $this->addAppendsToResults(collect([$result]));
        }

        if ($result instanceof Collection) {
            $this->addAppendsToResults($result);
        }

        if ($result instanceof Paginator || $result instanceof CursorPaginator) {
            $this->addAppendsToResults(collect($result->items()));
        }

        return $result;
    }
}
