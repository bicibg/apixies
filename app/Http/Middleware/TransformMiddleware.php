<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransformMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Let the controller & other middleware run first…
        $response = $next($request);

        // 1) If it’s not a JSON response, skip *all* transformation
        $contentType = $response->headers->get('Content-Type', '');
        if (! Str::startsWith($contentType, 'application/json')) {
            return $response;
        }

        // 2) Now we know it really *is* JSON—decode it
        $payload = json_decode($response->getContent(), true);

        // 3) If the payload is already wrapped, pass it through
        if (isset($payload['status'], $payload['code'])) {
            return $response;
        }

        // 4) Otherwise wrap it in your standard envelope
        return response()->json([
            'status' => 'success',
            'code'   => $response->status(),
            'data'   => $payload['data'] ?? $payload,
        ], $response->status())
            // preserve any request-ID header
            ->header('X-Request-ID', $response->headers->get('X-Request-ID'));
    }
}
