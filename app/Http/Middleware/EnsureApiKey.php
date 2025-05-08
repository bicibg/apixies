<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if this route should skip API key validation
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }

        // Get token from various sources
        $apiKey = $this->getApiKey($request);

        if (!$apiKey) {
            return ApiResponse::error(
                'API key is required',
                Response::HTTP_UNAUTHORIZED,
                [],
                'ERROR'
            );
        }

        // Validate the token
        $token = $this->validateToken($apiKey);

        if (!$token) {
            return ApiResponse::error(
                'Invalid API key',
                Response::HTTP_UNAUTHORIZED,
                [],
                'ERROR'
            );
        }

        // Set the authenticated user on the request
        $request->setUserResolver(function () use ($token) {
            return $token->tokenable;
        });

        // For Sanctum compatibility
        auth()->setUser($token->tokenable);

        return $next($request);
    }

    /**
     * Check if the request should skip API key validation
     *
     * @param Request $request
     * @return bool
     */
    private function shouldSkipValidation(Request $request): bool
    {
        // Public endpoints that don't require authentication
        $publicPaths = [
            'api/v1/health',
            'api/v1/ready',
            'api/v1/login',
            'api/v1/register',
            'api/v1/password/forgot',
            'api/v1/password/reset',
        ];

        foreach ($publicPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is for an authentication endpoint
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function isAuthEndpoint($request)
    {
        $authEndpoints = [
            'v1/login',
            'v1/register',
            'v1/password/forgot',
            'v1/password/reset',
        ];

        $path = $request->path();

        foreach ($authEndpoints as $endpoint) {
            if (str_contains($path, $endpoint)) {
                return true;
            }
        }

        return false;
    }
}
