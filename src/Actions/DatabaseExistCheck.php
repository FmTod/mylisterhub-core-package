<?php

namespace MyListerHub\Core\Actions;

use Illuminate\Support\Facades\DB;
use MyListerHub\Core\Concerns\Actions\AsAction;

class DatabaseExistCheck
{
    use AsAction;

    public function handle(string $databaseName): bool
    {
        $query = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?';
        $db = DB::select($query, [$databaseName]);

        return ! empty($db);
    }
}
