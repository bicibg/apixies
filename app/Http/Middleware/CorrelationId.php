<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Support\Facades\Log;

class CorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Generate or pull from incoming header
        $id = $request->header('X-Request-ID') ?: Str::uuid()->toString();

        // 2) Make it available for other services / logs
        app()->instance('X-Request-ID', $id);

        // 3) Dispatch the rest of the pipeline
        $response = $next($request);

        // 4) Only if we got a valid response object, set the header
        if ($response instanceof BaseResponse) {
            $response->headers->set('X-Request-ID', $id);
        } else {
            // for safety, log if something odd happened
            Log::warning('CorrelationId: pipeline returned non-Response', [
                'returned' => $response,
                'request'  => $request->path(),
            ]);
        }

        return $response;
    }
}
