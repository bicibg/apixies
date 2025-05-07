<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TransformMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->headers->get('Content-Type') === 'application/json') {
            $content = json_decode($response->getContent(), true);

            // Skip transformation if response already contains 'status' and 'code'
            if (isset($content['status'], $content['code'])) {
                return $response;
            }
        }

        // Otherwise, transform the response
        if ($response->isSuccessful()) {
            $content = json_decode($response->getContent(), true);

            return response()->json([
                'status' => $content['status'] ?? 'success',
                'code' => $response->status(),
                'data' => $content['data'] ?? null,
            ], $response->status());
        }

        return $response;
    }
}
