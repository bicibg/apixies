<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;

class ApiEndpointCounter
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Let everything else run first (including EnsureApiKey on protected routes)
        $response = $next($request);

        try {
            // 2) Only care about /api/* routes
            if (! $request->is('api/*')) {
                return $response;
            }

            // 3) Build your endpoint key
            $routeName = $request->route()?->getName();
            $endpoint  = $routeName ?: "{$request->method()} {$request->path()}";

            // 4) Increment aggregate counter
            ApiEndpointCount::upsert(
                [['endpoint' => $endpoint, 'count' => 1]],
                ['endpoint'],
                ['count' => DB::raw('count + 1')]
            );

            // 5) Resolve raw token -> PersonalAccessToken -> User
            $raw = $request->bearerToken() ?: $request->header('X-API-KEY');
            $pat = $raw
                ? PersonalAccessToken::findToken($raw)
                : null;

            $user     = $pat?->tokenable;
            $apiKeyId = $pat?->uuid;

            // 6) Create a log entry
            ApiEndpointLog::create([
                'endpoint'   => $endpoint,
                'user_id'    => $user?->id,
                'user_name'  => $user?->name,
                'api_key_id' => $apiKeyId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now(),
            ]);

        } catch (\Throwable $e) {
            // Never break the pipeline for logging failures
            Log::error('ApiEndpointCounter failed: '.$e->getMessage());
        }

        return $response;
    }
}
