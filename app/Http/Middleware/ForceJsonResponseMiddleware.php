<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->acceptsJson()) {
            return $next($request);
        }

        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
