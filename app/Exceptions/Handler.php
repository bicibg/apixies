<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse
     */
    public function render($request, Throwable $e): JsonResponse
    {
        $response = match (true) {
            $e instanceof ValidationException => [
                'status' => 'error',
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation failed.',
                'errors' => $e->validator->errors(),
            ],
            $e instanceof NotFoundHttpException => [
                'status' => 'error',
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Resource not found.',
            ],
            $e instanceof MethodNotAllowedHttpException => [
                'status' => 'error',
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => 'Method not allowed.',
            ],
            $e instanceof UnauthorizedHttpException => [
                'status' => 'error',
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized access.',
            ],
            default => [
                'status' => 'error',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An unexpected error occurred.',
                'details' => config('app.debug') ? $e->getMessage() : 'Please contact support.',
            ],
        };

        return response()->json($response, $response['code']);
    }
}
