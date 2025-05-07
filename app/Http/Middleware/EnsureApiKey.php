<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;

class EnsureApiKey
{
    public function handle($req, Closure $next)
    {
        if ($req->header('X-API-KEY') !== config('app.api_key')) {
            return ApiResponse::error('Invalid API key', 401);
        }
        return $next($req);
    }
}
