<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;

class ApiEndpointCounter
{
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*')) {
            // 1) Determine the endpoint identifier
            $routeName = $request->route()?->getName();
            $routeKey  = $routeName ?: $request->method().' '.$request->path();

            // 2) Increment the aggregate counter
            ApiEndpointCount::upsert(
                [['endpoint' => $routeKey, 'count' => 1]],
                ['endpoint'],
                ['count' => \DB::raw('count + 1')]
            );

            // 3) Capture log details
            $user       = $request->user();
            $apiKey     = method_exists($user, 'currentAccessToken')
                ? $user->currentAccessToken()?->id
                : null;

            ApiEndpointLog::create([
                'endpoint'    => $routeKey,
                'user_id'     => $user?->id,
                'user_name'   => $user?->name,
                'api_key_id'  => $apiKey,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->header('User-Agent'),
            ]);
        }

        return $next($request);
    }
}
