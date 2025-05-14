<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\SandboxToken;
use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Grab the token from the Authorization header or X-API-KEY
        $raw = $request->bearerToken() ?: $request->header('X-API-KEY');

        if (!$raw) {
            Log::warning('API key missing', ['path' => $request->path()]);
            return ApiResponse::error('No API key provided', Response::HTTP_UNAUTHORIZED);
        }

        // Debug info
        $debug = ['token_prefix' => substr($raw, 0, 8) . '...', 'path' => $request->path()];

        // 2) Check for a sandbox token
        $sandbox = SandboxToken::where('token', $raw)->first();

        if ($sandbox) {
            // Handle sandbox token
            Log::info('Sandbox token found', $debug);

            // Check if expired
            if (isset($sandbox->expires_at) && now()->greaterThan($sandbox->expires_at)) {
                Log::warning('Sandbox token expired', $debug);
                return ApiResponse::error('Sandbox token expired', Response::HTTP_UNAUTHORIZED);
            }

            // Determine which field to use for call tracking
            // Check all possible field names for tracking calls
            $callsRemaining = null;
            $callsField = null;

            if (isset($sandbox->remaining_calls)) {
                $callsRemaining = $sandbox->remaining_calls;
                $callsField = 'remaining_calls';
            } elseif (isset($sandbox->calls)) {
                // If calls is a counter of used calls
                $quota = $sandbox->quota ?? 100; // Default quota if not set
                $callsRemaining = $quota - $sandbox->calls;
                $callsField = 'calls';
            } elseif (isset($sandbox->quota)) {
                $callsRemaining = $sandbox->quota;
                $callsField = 'quota';
            }

            // Log the full sandbox token data for debugging
            Log::info('Sandbox token data', [
                'token' => substr($raw, 0, 8) . '...',
                'data' => $sandbox->toArray()
            ]);

            // Check if we have a valid tracking field
            if ($callsField && $callsRemaining !== null) {
                // Check quota
                if ($callsRemaining <= 0) {
                    Log::warning('Sandbox quota exhausted', $debug);
                    return ApiResponse::error('Sandbox quota exhausted', Response::HTTP_TOO_MANY_REQUESTS);
                }

                // Update call count based on field type
                if ($callsField === 'remaining_calls' || $callsField === 'quota') {
                    $sandbox->$callsField = $callsRemaining - 1;
                } elseif ($callsField === 'calls') {
                    $sandbox->calls += 1;
                }

                $sandbox->save();
            } else {
                // If no tracking field is found, just proceed without tracking
                Log::warning('No call tracking field found on sandbox token', $debug);
            }

            // For sandbox tokens, we don't need a user, so we can proceed
            Log::info('Sandbox token authorized', $debug);
            return $next($request);
        }

        // 3) Standard API token lookup via Sanctum
        $cacheKey = "api_token:{$raw}";
        $token = Cache::get($cacheKey);
        if (!$token) {
            $token = PersonalAccessToken::findToken($raw);
            if ($token) {
                Cache::put($cacheKey, $token, 60);
            }
        }

        if (!$token) {
            Log::warning('API key invalid', ['token_hash' => hash('sha256', $raw)]);
            return ApiResponse::error('Invalid API key', Response::HTTP_UNAUTHORIZED);
        }

        // 4) Expiration check
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            Log::warning('API key expired', ['uuid' => $token->uuid]);
            return ApiResponse::error('API key expired', Response::HTTP_UNAUTHORIZED);
        }

        // 5) Scope check
        if (!$token->can('read')) {
            Log::warning('API key missing read scope', ['uuid' => $token->uuid]);
            return ApiResponse::error('Insufficient scope', Response::HTTP_FORBIDDEN);
        }

        // 6) All good — update last_used_at and set the user
        $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();
        $request->setUserResolver(fn() => $token->tokenable);

        Log::info('API key authorized', ['uuid' => $token->uuid, 'path' => $request->path()]);

        return $next($request);
    }
}
