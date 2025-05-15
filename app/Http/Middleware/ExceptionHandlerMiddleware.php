<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExceptionHandlerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            // Log the exception with context
            Log::error('Uncaught exception in middleware', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : ($e instanceof \Illuminate\Auth\AuthenticationException
                    ? Response::HTTP_UNAUTHORIZED
                    : ($e instanceof \Illuminate\Auth\Access\AuthorizationException
                        ? Response::HTTP_FORBIDDEN
                        : Response::HTTP_INTERNAL_SERVER_ERROR));

            // In production, don't expose detailed error messages
            $details = config('app.debug') ? $e->getMessage() : 'An error occurred while processing your request.';

            return response()->json([
                'status' => 'error',
                'code' => $statusCode,
                'message' => $this->getReadableMessage($statusCode),
                'details' => $details,
            ], $statusCode);
        }
    }

    /**
     * Get a human-readable message for status codes
     */
    private function getReadableMessage(int $statusCode): string
    {
        return match($statusCode) {
            Response::HTTP_UNAUTHORIZED => 'Authentication required',
            Response::HTTP_FORBIDDEN => 'You do not have permission to access this resource',
            Response::HTTP_NOT_FOUND => 'The requested resource was not found',
            Response::HTTP_TOO_MANY_REQUESTS => 'Rate limit exceeded',
            default => 'An unexpected error occurred'
        };
    }
}
