<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Let the request run through all other middleware / controllers:
        $rawResponse = $next($request);

        // If it’s not already a Response, wrap it in one:
        if (! $rawResponse instanceof BaseResponse) {
            $response = response($rawResponse);
        } else {
            $response = $rawResponse;
        }

        // Now safely add headers:
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=63072000; includeSubDomains; preload'
        );
        $response->headers->set('Permissions-Policy', 'geolocation=()');

        return $response;
    }
}
