<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success payload.
     *
     * @param mixed  $data
     * @param string $message
     * @param string $code    Machine-readable code
     * @return JsonResponse
     */
    public static function success($data = [], string $message = 'OK', string $code = 'SUCCESS'): JsonResponse
    {
        return response()->json([
            'status'    => 'success',
            'http_code' => 200,
            'code'      => $code,
            'message'   => $message,
            'data'      => $data,
        ], 200)
            ->header('X-Request-ID', request()->header('X-Request-ID'));
    }

    /**
     * Error payload.
     *
     * @param string      $message
     * @param int         $status    HTTP status
     * @param array       $errors
     * @param string      $code      Machine code
     * @param string|null $docs      Link to docs
     * @return JsonResponse
     */
    public static function error(
        string $message = 'Error',
        int    $status  = 400,
        array  $errors  = [],
        string $code    = 'ERROR',
        string $docs    = null
    ): JsonResponse {
        $payload = [
            'status'    => 'error',
            'http_code' => $status,
            'code'      => $code,
            'message'   => $message,
        ];
        if ($errors) $payload['errors'] = $errors;
        if ($docs)   $payload['docs']   = $docs;

        return response()->json($payload, $status)
            ->header('X-Request-ID', request()->header('X-Request-ID'));
    }
}
