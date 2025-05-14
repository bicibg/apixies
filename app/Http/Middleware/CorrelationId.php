<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Support\Facades\Log;

class CorrelationId
{
    public const REQUEST_ID_KEY = 'correlation-id';

    public function handle(Request $request, Closure $next)
    {
        // 1) Generate or pull from incoming header
        $id = $request->header('X-Request-ID');

        // Validate the format if present, otherwise generate a new one
        if (!$id || !Str::isUuid($id)) {
            $id = Str::uuid()->toString();
        }

        // 2) Make it available for other services / logs
        // Use a proper service key instead of a header name
        app()->instance(self::REQUEST_ID_KEY, $id);

        // 3) Add to all logs
        Log::withContext(['correlation_id' => $id]);

        // 4) Dispatch the rest of the pipeline
        $response = $next($request);

        // 5) Only if we got a valid response object, set the header
        if ($response instanceof BaseResponse) {
            $response->headers->set('X-Request-ID', $id);
        } else {
            // Log warning but don't try to convert non-Response objects
            Log::warning('CorrelationId: pipeline returned non-Response', [
                'returned' => is_object($response) ? get_class($response) : gettype($response),
                'request'  => $request->path(),
            ]);
        }

        return $response;
    }
}
