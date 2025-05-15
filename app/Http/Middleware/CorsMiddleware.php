<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $allowedOrigins = config('cors.allowed_origins', []);
        $origin = $request->header('Origin');

        if ($origin && (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins))) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        } elseif (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods',
            implode(', ', config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])));

        $response->headers->set('Access-Control-Allow-Headers',
            implode(', ', config('cors.allowed_headers', ['Content-Type', 'X-Requested-With', 'Authorization', 'X-API-KEY'])));

        $response->headers->set('Access-Control-Max-Age',
            config('cors.max_age', 86400));

        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(204);
            $response->setContent('');
        }

        return $response;
    }
}
