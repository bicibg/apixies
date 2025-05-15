<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\{
    MethodNotAllowedHttpException,
    NotFoundHttpException,
    UnauthorizedHttpException
};
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if (!$request->expectsJson() && !$request->is('api/*')) {
            if ($e instanceof AuthenticationException) {
                return $this->unauthenticated($request, $e);
            }

            return parent::render($request, $e);
        }

        return match(true) {
            $e instanceof ValidationException => ApiResponse::error(
                'Validation failed.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->errors(),
                'VALIDATION_FAILED'
            ),
            $e instanceof NotFoundHttpException => ApiResponse::error(
                'Resource not found.',
                Response::HTTP_NOT_FOUND,
                [],
                'NOT_FOUND'
            ),
            $e instanceof MethodNotAllowedHttpException => ApiResponse::error(
                'Method not allowed.',
                Response::HTTP_METHOD_NOT_ALLOWED,
                [],
                'METHOD_NOT_ALLOWED'
            ),
            $e instanceof UnauthorizedHttpException => ApiResponse::error(
                'Unauthorized access.',
                Response::HTTP_UNAUTHORIZED,
                [],
                'UNAUTHORIZED'
            ),
            $e instanceof AuthenticationException => ApiResponse::error(
                'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
                [],
                'UNAUTHENTICATED'
            ),
            default => ApiResponse::error(
                config('app.debug') ? $e->getMessage() : 'Please contact support.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [],
                'INTERNAL_ERROR',
            ),
        };
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return ApiResponse::error('Unauthenticated.', Response::HTTP_UNAUTHORIZED, [], 'UNAUTHENTICATED');
        }

        return redirect()->guest(route('login'));
    }
}
