<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

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
        // Debug incoming request
        Log::info('API request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'authorization' => $request->header('Authorization'),
            'x-api-key' => $request->header('X-API-Key'),
            'has_bearer' => $request->bearerToken() ? true : false,
            'all_headers' => $request->headers->all()
        ]);

        // Skip for certain routes that don't require authentication
        if ($this->shouldSkipValidation($request)) {
            Log::info('Skipping validation for public route');
            return $next($request);
        }

        // Get token from various sources
        $apiKey = $this->getApiKey($request);

        if (!$apiKey) {
            Log::info('No API key found in request');
            return ApiResponse::error(
                'API key is required',
                Response::HTTP_UNAUTHORIZED,
                [],
                'ERROR'
            );
        }

        Log::info('API key found', ['key_length' => strlen($apiKey)]);

        // Validate the token
        $token = $this->validateToken($apiKey);

        if (!$token) {
            Log::info('Invalid API key provided');
            return ApiResponse::error(
                'Invalid API key',
                Response::HTTP_UNAUTHORIZED,
                [],
                'ERROR'
            );
        }

        Log::info('Valid API key', ['user' => $token->tokenable->email]);

        // Set the authenticated user on the request
        $request->setUserResolver(function () use ($token) {
            return $token->tokenable;
        });

        // For Sanctum compatibility
        auth()->setUser($token->tokenable);

        return $next($request);
    }

    /**
     * Get API key from request
     *
     * @param Request $request
     * @return string|null
     */
    private function getApiKey(Request $request): ?string
    {
        // Check header: X-API-Key
        $apiKey = $request->header('X-API-Key');
        if ($apiKey) {
            Log::info('Found API key in X-API-Key header');
            return $apiKey;
        }

        // Direct check for Authorization header
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $bearerToken = substr($authHeader, 7);
            Log::info('Found bearer token directly from Authorization header');
            return $bearerToken;
        }

        // Laravel's built-in bearer token method
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            Log::info('Found bearer token using request->bearerToken()');
            return $bearerToken;
        }

        // Check query parameter: api_key
        $queryApiKey = $request->query('api_key');
        if ($queryApiKey) {
            Log::info('Found API key in query parameter');
            return $queryApiKey;
        }

        return null;
    }

    /**
     * Validate the token
     *
     * @param string $apiKey
     * @return PersonalAccessToken|null
     */
    private function validateToken(string $apiKey): ?PersonalAccessToken
    {
        // If token contains a pipe character, it's a full Sanctum token
        if (str_contains($apiKey, '|')) {
            [$id, $token] = explode('|', $apiKey, 2);

            Log::info('Validating token with ID', ['id' => $id]);

            // Find the token by ID
            $accessToken = PersonalAccessToken::find($id);

            // Verify the token hash
            if ($accessToken && hash_equals($accessToken->token, hash('sha256', $token))) {
                return $accessToken;
            }

            Log::info('Token validation failed for ID', ['id' => $id]);
            return null;
        }

        // If there's no pipe, check if it's a plain token
        Log::info('Searching for token by hash');
        $hashedToken = hash('sha256', $apiKey);
        $token = PersonalAccessToken::where('token', $hashedToken)->first();

        if ($token) {
            Log::info('Found token by hash');
        } else {
            Log::info('No token found by hash');
        }

        return $token;
    }

    /**
     * Determine if the request should pass through authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function shouldSkipValidation(Request $request): bool
    {
        // Add any path patterns that should bypass API key auth
        $publicPaths = [
            'api/v1/health',
            'api/v1/ready',
            'api/v1/login',
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
}
