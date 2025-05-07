<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $res = $next($request);

        return $res
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload')
            ->header('Permissions-Policy', 'geolocation=()');
    }
}
