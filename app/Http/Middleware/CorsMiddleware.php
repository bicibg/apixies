<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Determine allowed origins
        $allowedOrigins = config('cors.allowed_origins', []);
        $origin = $request->header('Origin');

        // Configure allowed origins - don't use '*' in production
        if ($origin && (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins))) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        } elseif (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        // Configure allowed methods
        $response->headers->set('Access-Control-Allow-Methods',
            implode(', ', config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])));

        // Configure allowed headers
        $response->headers->set('Access-Control-Allow-Headers',
            implode(', ', config('cors.allowed_headers', ['Content-Type', 'X-Requested-With', 'Authorization', 'X-API-KEY'])));

        // Configure max age for preflight requests
        $response->headers->set('Access-Control-Max-Age',
            config('cors.max_age', 86400));

        // For preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(204);
            $response->setContent('');
        }

        return $response;
    }
}
