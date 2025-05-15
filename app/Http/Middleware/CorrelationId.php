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
        $id = $request->header('X-Request-ID');

        if (!$id || !Str::isUuid($id)) {
            $id = Str::uuid()->toString();
        }

        app()->instance(self::REQUEST_ID_KEY, $id);

        Log::withContext(['correlation_id' => $id]);

        $response = $next($request);

        if ($response instanceof BaseResponse) {
            $response->headers->set('X-Request-ID', $id);
        } else {
            Log::warning('CorrelationId: pipeline returned non-Response', [
                'returned' => is_object($response) ? get_class($response) : gettype($response),
                'request'  => $request->path(),
            ]);
        }

        return $response;
    }
}
