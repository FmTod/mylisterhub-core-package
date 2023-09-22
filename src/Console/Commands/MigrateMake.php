<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;

class MigrateMake extends MigrateMakeCommand
{
    protected $signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}
        {--tenant : Create migration file in the tenant migrations directory}';

    /**
     * Get migration path (either specified by '--path' option or default location).
     */
    protected function getMigrationPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        if ($this->hasOption('tenant') && $this->option('tenant')) {
            return parent::getMigrationPath().DIRECTORY_SEPARATOR.'tenant';
        }

        return parent::getMigrationPath();
    }
}
