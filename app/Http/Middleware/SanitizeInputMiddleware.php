<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInputMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Apply trim to all input fields
        $sanitizedInput = array_map('trim', $request->all());
        $request->merge($sanitizedInput);

        return $next($request);
    }
}
