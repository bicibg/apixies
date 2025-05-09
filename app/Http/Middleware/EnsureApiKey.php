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
        $public = [
            'api/v1/login', 'api/v1/register',
            'api/v1/password/forgot', 'api/v1/password/reset',
        ];

        foreach ($public as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        $raw = $request->bearerToken() ?: $request->header('X-API-KEY');
        if (! $raw) {
            return ApiResponse::error('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $token = Cache::remember("api_token:{$raw}", 60, function () use ($raw) {
            return PersonalAccessToken::where('uuid', $raw)->first();
        });

        if (! $token || $token->expired()) {
            return ApiResponse::error('Invalid or expired token', Response::HTTP_UNAUTHORIZED);
        }

        if (! $token->can('read')) {
            return ApiResponse::error('Insufficient scope', Response::HTTP_FORBIDDEN);
        }

        $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();

        $request->setUserResolver(fn() => $token->tokenable);

        Log::info('API request', [
            'path'      => $request->path(),
            'method'    => $request->method(),
            'client_ip' => $request->ip(),
            'token_uuid'=> $token->uuid,
        ]);

        return $next($request);
    }
}
