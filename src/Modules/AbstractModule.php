<?php

namespace MyListerHub\Core\Modules;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Inertia\Inertia;

abstract class AbstractModule
{
    protected string $name;

    protected array $composers = [];

    protected array $providers = [];

    protected array $aliases = [];

    protected array $commands = [];

    protected array $files = [];

    protected array $config = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function boot(): void
    {
        foreach ($this->composers as $view => $composer) {
            Inertia::composer("$this->name::$view", $composer);
        }
    }

    public function register(): void
    {
        foreach ($this->providers as $provider) {
            App::register($provider);
        }

        foreach ($this->aliases as $aliasName => $aliasClass) {
            App::alias($aliasName, $aliasClass);
        }

        foreach ($this->files as $file) {
            $path = __DIR__;

            include "$path/$file";
        }

        Config::set(Str::snake($this->name), $this->config);
    }
}
