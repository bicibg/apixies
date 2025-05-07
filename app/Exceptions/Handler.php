<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
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
            default => ApiResponse::error(
                config('app.debug') ? $e->getMessage() : 'Please contact support.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [],
                'INTERNAL_ERROR',
                url('/docs#errors-500')
            ),
        };
    }
}
