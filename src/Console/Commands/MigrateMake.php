<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;

class MigrateMake extends MigrateMakeCommand
{
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        $this->signature .= '
                {--tenant : Create migration file in the tenant migrations directory.}
        ';

        parent::__construct($creator, $composer);
    }

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
