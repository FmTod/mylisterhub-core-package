<?php

namespace MyListerHub\Core\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Boot services.
     *
     * @throws \JsonException
     */
    public function boot(): void
    {
        $modules = glob(base_path('modules/*'), GLOB_ONLYDIR);

        foreach ($modules as $path) {
            if (! file_exists($definition = "$path/module.php")) {
                return;
            }

            /** @var \Modules\AbstractModule $module */
            $module = include $definition;
            $module->boot();

            if (file_exists("$path/Resources/views") && is_dir("$path/Resources/views")) {
                $this->loadViewsFrom("$path/Resources/views", $module->getName());
            }
        }
    }

    /**
     * Register services.
     *
     * @throws \JsonException
     */
    public function register(): void
    {
        $modules = glob(base_path('modules/*'), GLOB_ONLYDIR);

        foreach ($modules as $path) {
            if (! file_exists($definition = "$path/module.php")) {
                return;
            }

            /** @var \Modules\AbstractModule $module */
            $module = include $definition;
            $module->register();

            $this->commands($module->getCommands());

            if (file_exists("$path/Database/Migrations/Central") && is_dir("$path/Database/Migrations/Central")) {
                $this->loadMigrationsFrom("$path/Database/Migrations/Central");
            }
        }
    }
}
