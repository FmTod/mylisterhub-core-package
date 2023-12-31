<?php

namespace MyListerHub\Core\Concerns\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsFake;
use Lorisleiva\Actions\Concerns\AsJob;
use Lorisleiva\Actions\Concerns\AsObject;

trait AsAction
{
    use AsFake;
    use AsJob;
    use AsObject;

    /**
     * @throws \Throwable
     */
    public static function runWithTransaction(...$arguments): mixed
    {
        return DB::transaction(static fn () => static::make()->run(...$arguments));
    }
}
