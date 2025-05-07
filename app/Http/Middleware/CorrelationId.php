<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->header('X-Request-ID') ?: Str::uuid()->toString();
        // Share with logs and response
        app()->instance('X-Request-ID', $id);
        $response = $next($request);
        return $response->header('X-Request-ID', $id);
    }
}
