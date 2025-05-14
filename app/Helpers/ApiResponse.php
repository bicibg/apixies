<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiResponse
{
    public static function success($data = null, string $message = 'OK', string $code = 'SUCCESS', int $status = 200): JsonResponse
    {
        $payload = [
            'status'    => 'success',
            'http_code' => $status,
            'code'      => $code,
            'message'   => $message,
        ];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return self::withRequestId(response()->json($payload, $status));
    }

    public static function error(string $message = 'Error', int $status = 400, array $errors = [], string $code = 'ERROR', string $docs = null): JsonResponse
    {
        $payload = [
            'status'    => 'error',
            'http_code' => $status,
            'code'      => $code,
            'message'   => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        if ($docs) {
            $payload['docs'] = $docs;
        }

        return self::withRequestId(response()->json($payload, $status));
    }

    protected static function withRequestId(JsonResponse $response): JsonResponse
    {
        $requestId = request()->header('X-Request-ID');

        if (!is_string($requestId) || !Str::uuid()->isValid($requestId)) {
            $requestId = (string) Str::uuid();
        }

        return $response->header('X-Request-ID', $requestId);
    }
}
