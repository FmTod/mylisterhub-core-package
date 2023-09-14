<?php

namespace MyListerHub\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HSTS
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (config('hsts.enabled')) {
            $maxAge = config('hsts.max_age');
            $includeSubdomains = config('hsts.include_subdomains') ? '; includeSubdomains' : '';
            $preload = config('hsts.preload') ? '; preload' : '';

            $response = $next($request);
            $response->header('Strict-Transport-Security', 'max-age='.$maxAge.$includeSubdomains.$preload);

            return $response;
        }

        return $next($request);
    }
}
