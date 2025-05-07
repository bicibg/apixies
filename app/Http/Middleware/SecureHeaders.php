<?php

namespace App\Http\Middleware;

use Closure;

class SecureHeaders
{
    public function handle($req, Closure $next)
    {
        $res = $next($req);

        return $res
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Referrer-Policy', 'no-referrer-when-downgrade')
            ->header('Content-Security-Policy', "default-src 'self'")
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
