<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EnsureApiKey
{
    /**
     * Handle an incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('sandbox_mode', false);
        $path = $request->path();

        // Log for debugging
        Log::debug("EnsureApiKey handling request for path: {$path}");

        if ($request->route() && $request->route()->getAction('middleware') &&
            in_array('public', (array) $request->route()->getAction('middleware'))) {
            return $next($request);
        }

        $isHealthOrReadinessEndpoint =
            $path === 'api/v1/health' ||
            $path === 'api/v1/ready' ||
            $path === 'health' ||
            $path === 'ready';

        $sandboxToken = $request->header('X-Sandbox-Token');
        if ($sandboxToken) {
            Log::debug("Found sandbox token for path: {$path}");

            try {
                $token = DB::table('sandbox_tokens')
                    ->where('token', $sandboxToken)
                    ->first();

                if (!$token) {
                    Log::info("Invalid sandbox token provided");
                    return response()->json([
                        'status' => 'error',
                        'code' => 'INVALID_SANDBOX_TOKEN',
                        'message' => 'Invalid sandbox token',
                    ], 401);
                }

                $isExpired = property_exists($token, 'expires_at') &&
                    $token->expires_at &&
                    now()->greaterThan($token->expires_at);

                $isQuotaExceeded = $token->calls >= $token->quota;

                $request->attributes->set('sandbox_mode', true);
                Log::debug("Set sandbox_mode=true for path: {$path}");

                $request->attributes->set('token_info', [
                    'remaining_calls' => max(0, $token->quota - $token->calls),
                    'expires_at' => $token->expires_at,
                    'is_expired' => $isExpired
                ]);

                if ($isHealthOrReadinessEndpoint) {
                    // Don't increment token usage for health/ready endpoints
                    return $next($request);
                }

                if ($isExpired) {
                    Log::info("Sandbox token expired");
                    return response()->json([
                        'status' => 'error',
                        'code' => 'SANDBOX_TOKEN_EXPIRED',
                        'message' => 'Sandbox token expired. Please try again tomorrow.',
                        'remaining_calls' => max(0, $token->quota - $token->calls),
                        'expired' => true
                    ], 401);
                }

                if ($isQuotaExceeded) {
                    Log::info("Sandbox quota exceeded");
                    return response()->json([
                        'status' => 'error',
                        'code' => 'SANDBOX_QUOTA_EXCEEDED',
                        'message' => 'Sandbox quota exhausted. Please try again tomorrow.',
                        'quota_exceeded' => true
                    ], 429);
                }

                // Increment token usage count for regular API endpoints
                DB::table('sandbox_tokens')
                    ->where('token', $sandboxToken)
                    ->increment('calls', 1, [
                        'updated_at' => now()
                    ]);

                Log::debug("Incremented usage count for sandbox token");
                return $next($request);
            } catch (\Exception $e) {
                Log::error('Error processing sandbox token: ' . $e->getMessage());

                return response()->json([
                    'status' => 'error',
                    'code' => 'SANDBOX_ERROR',
                    'message' => 'Error processing sandbox token',
                ], 500);
            }
        }

        if ($isHealthOrReadinessEndpoint) {
            return $next($request);
        }

        if (str_starts_with($path, 'docs') ||
            str_starts_with($path, 'swagger')) {
            return $next($request);
        }

        if (!$request->bearerToken() && !$request->header('X-API-Key')) {
            return response()->json([
                'status' => 'error',
                'code' => 'MISSING_AUTH',
                'message' => 'Missing API key or token',
            ], 401);
        }

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
