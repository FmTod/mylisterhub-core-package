<?php

namespace MyListerHub\Core;

use MyListerHub\Core\Console\Commands\DatabaseOptimize;
use MyListerHub\Core\Console\Commands\DataObjectMake;
use MyListerHub\Core\Console\Commands\HorizonPrune;
use MyListerHub\Core\Console\Commands\LogClear;
use MyListerHub\Core\Console\Commands\MigrateMake;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CoreServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mylisterhub-core')
            ->hasCommands([
                DatabaseOptimize::class,
                DataObjectMake::class,
                HorizonPrune::class,
                LogClear::class,
                MigrateMake::class,
            ]);
    }
}
