<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class EnsureApiKey
{
    public function handle($request, Closure $next)
    {
        // Skip API key check for authentication endpoints
        if ($this->isAuthEndpoint($request)) {
            return $next($request);
        }

        // Check if user is already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        // Check for API key in the header
        $apiKey = $request->header('Authorization');

        // If no API key, try X-API-KEY header for backward compatibility
        if (!$apiKey && $request->header('X-API-KEY')) {
            $apiKey = 'Bearer ' . $request->header('X-API-KEY');
        }

        if (!$apiKey) {
            return ApiResponse::error('API key is required', 401);
        }

        // Extract the token from the Bearer format
        $tokenValue = str_replace('Bearer ', '', $apiKey);

        // Find the token in the database
        $token = PersonalAccessToken::findToken($tokenValue);

        if (!$token) {
            return ApiResponse::error('Invalid API key', 401);
        }

        // Get the user associated with the token
        $user = $token->tokenable;

        if (!$user) {
            return ApiResponse::error('User not found', 401);
        }

        // Update last_used_at timestamp
        $token->forceFill(['last_used_at' => now()])->save();

        // Set the authenticated user
        Auth::setUser($user);

        return $next($request);
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
