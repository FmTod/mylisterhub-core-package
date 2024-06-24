<?php

namespace MyListerHub\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ModuleRoutes
{
    public function __construct(public ?string $module = null) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->module) {
            $response = $next($request);
            $response->headers->set('X-Module', $this->module);

            Inertia::share('module', $this->module);

            return $response;
        }

        return $next($request);
    }
}
