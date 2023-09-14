<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class DataObjectMake extends GeneratorCommand
{
    /**
     * The name  of the console command.
     *
     * @var string
     */
    protected $name = 'make:dto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new Data Object class';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return base_path('stubs/dto.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return "$rootNamespace\\DataObjects";
    }
}
