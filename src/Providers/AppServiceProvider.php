<?php

namespace MyListerHub\Core\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        JsonResource::withoutWrapping();
        Schema::defaultStringLength(191);
        Model::preventLazyLoading(! App::isProduction());

        if (! request()->server->has('HTTP_X_FORWARDED_PROTO')) {
            return;
        }

        URL::forceScheme(request()->server->get('HTTP_X_FORWARDED_PROTO'));
    }
}
