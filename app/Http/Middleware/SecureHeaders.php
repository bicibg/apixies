<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $rawResponse = $next($request);

        if (! $rawResponse instanceof BaseResponse) {
            $response = response($rawResponse);
        } else {
            $response = $rawResponse;
        }

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=63072000; includeSubDomains; preload'
        );

        // Enhanced permissions policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=()');

        // Add Content-Security-Policy
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; connect-src 'self'"
        );

        return $response;
    }
}
