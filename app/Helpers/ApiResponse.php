<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data=[], $message='OK', $code='SUCCESS')
    {
        return response()->json([
            'status'    => 200,
            'code'      => $code,
            'message'   => $message,
            'data'      => $data,
        ], 200);
    }

    public static function error($message='Error', $status=400, $errors=[], $code='ERROR', $docs=null)
    {
        $payload = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
        ];
        if ($errors)  $payload['errors'] = $errors;
        if ($docs)    $payload['docs']   = $docs;

        return response()->json($payload, $status);
    }
}
