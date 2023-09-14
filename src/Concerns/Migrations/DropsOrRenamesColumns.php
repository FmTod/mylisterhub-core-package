<?php

namespace MyListerHub\Core\Concerns\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aims to fix issue with multiple drop/rename statements under one schema closure.
 *
 * @link https://github.com/laravel/framework/issues/2979
 */
trait DropsOrRenamesColumns
{
    /**
     * Indicate that the given columns should be dropped.
     */
    public function dropColumn(string|Blueprint $table, string $column): void
    {
        if ($table instanceof Blueprint) {
            $table = $table->getTable();
        }

        if (Schema::hasColumn($table, $column)) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });
        }
    }

    /**
     * Indicate that the given columns should be renamed.
     */
    public function renameColumn(string|Blueprint $table, string $from, string $to): void
    {
        if ($table instanceof Blueprint) {
            $table = $table->getTable();
        }

        if (Schema::hasColumn($table, $from)) {
            Schema::table($table, function (Blueprint $blueprint) use ($from, $to) {
                $blueprint->renameColumn($from, $to);
            });
        }
    }
}
