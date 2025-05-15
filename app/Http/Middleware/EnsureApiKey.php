<?php

namespace App\Http\Middleware;

use App\Models\SandboxToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Check if the route should be public
        if ($request->route()->getAction('middleware') &&
            in_array('public', (array) $request->route()->getAction('middleware'))) {
            return $next($request);
        }

        // Skip auth for test/health/status/docs routes
        $path = $request->path();
        if (str_starts_with($path, 'health') ||
            str_starts_with($path, 'ready') ||
            str_starts_with($path, 'docs') ||
            str_starts_with($path, 'swagger')) {
            return $next($request);
        }

        // Check for Sandbox Token
        $sandboxToken = $request->header('X-Sandbox-Token');
        if ($sandboxToken) {
            Log::info('Using sandbox token: ' . substr($sandboxToken, 0, 8) . '...');

            try {
                $token = SandboxToken::where('token', $sandboxToken)->first();

                if (!$token) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'INVALID_SANDBOX_TOKEN',
                        'message' => 'Invalid sandbox token',
                    ], 401);
                }

                // Check if expires_at column exists
                $hasExpiresAt = \Schema::hasColumn('sandbox_tokens', 'expires_at');

                // Check if token is expired (if column exists)
                if ($hasExpiresAt && $token->expires_at && now()->greaterThan($token->expires_at)) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'SANDBOX_TOKEN_EXPIRED',
                        'message' => 'Sandbox token expired',
                    ], 401);
                }

                // Check if quota is exceeded
                if ($token->calls >= $token->quota) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'SANDBOX_QUOTA_EXCEEDED',
                        'message' => 'Sandbox quota exhausted',
                    ], 429);
                }

                // Enable sandbox mode
                $request->attributes->set('sandbox_mode', true);

                return $next($request);
            } catch (\Exception $e) {
                Log::error('Error validating sandbox token: ' . $e->getMessage());

                return response()->json([
                    'status' => 'error',
                    'code' => 'SANDBOX_ERROR',
                    'message' => 'Error processing sandbox token',
                ], 500);
            }
        }

        // Always allow direct access to test endpoint
        if ($path === 'api/v1/test') {
            $request->attributes->set('sandbox_mode', true);
            return $next($request);
        }

        // If we get here and there's no sandbox token, treat as bearer token
        if (!$request->bearerToken() && !$request->header('X-API-Key')) {
            return response()->json([
                'status' => 'error',
                'code' => 'MISSING_AUTH',
                'message' => 'Missing API key or token',
            ], 401);
        }

        // Prefer Bearer token if available
        if ($request->bearerToken()) {
            try {
                $user = Auth::guard('sanctum')->user();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'INVALID_TOKEN',
                        'message' => 'Invalid API token',
                    ], 401);
                }

                return $next($request);
            } catch (\Exception $e) {
                Log::error('Error validating bearer token: ' . $e->getMessage());

                return response()->json([
                    'status' => 'error',
                    'code' => 'AUTH_ERROR',
                    'message' => 'Authentication error: ' . $e->getMessage(),
                ], 401);
            }
        }

        // Check for API key
        $apiKey = $request->header('X-API-Key');
        if ($apiKey) {
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_API_KEY',
                'message' => 'Invalid API key',
            ], 401);
        }

        return response()->json([
            'status' => 'error',
            'code' => 'AUTH_REQUIRED',
            'message' => 'Authentication required',
        ], 401);
    }
}
