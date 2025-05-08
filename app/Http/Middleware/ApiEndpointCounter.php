<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiEndpointCount;

class ApiEndpointCounter
{
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*')) {
            // Use named route if available, else HTTP method + path
            $route = $request->route()?->getName()
                ?: $request->method().' '.$request->path();

            // Upsert: increment count atomically
            ApiEndpointCount::upsert([
                ['endpoint' => $route, 'count' => 1]
            ], ['endpoint'], ['count' => \DB::raw('count + 1')]);
        }

        return $next($request);
    }
}
