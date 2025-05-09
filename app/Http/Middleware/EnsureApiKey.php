<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
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

        if (! $raw) {
            Log::warning('API key missing', ['path' => $request->path()]);
            return ApiResponse::error('No API key provided', Response::HTTP_UNAUTHORIZED);
        }

        // 2) Lookup via Sanctum helper, cached for 60s
        $cacheKey = "api_token:{$raw}";
        $token = Cache::remember($cacheKey, 60, fn() => PersonalAccessToken::findToken($raw));

        if (! $token) {
            Log::warning('API key invalid', ['token' => $raw]);
            return ApiResponse::error('Invalid API key', Response::HTTP_UNAUTHORIZED);
        }

        // 3) Expiration check
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            Log::warning('API key expired', ['uuid' => $token->uuid]);
            return ApiResponse::error('API key expired', Response::HTTP_UNAUTHORIZED);
        }

        // 4) Scope check
        if (! $token->can('read')) {
            Log::warning('API key missing read scope', ['uuid' => $token->uuid]);
            return ApiResponse::error('Insufficient scope', Response::HTTP_FORBIDDEN);
        }

        // 5) All good — update last_used_at quietly and set the user
        $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();
        $request->setUserResolver(fn() => $token->tokenable);

        Log::info('API key authorized', ['uuid' => $token->uuid, 'path' => $request->path()]);

        return $next($request);
    }
}
