<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\SslHealthInspectorService;

class SslHealthInspectorController extends Controller
{
    public function __invoke(Request $request, SslHealthInspectorService $inspector)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'regex:/^([a-z0-9-]+\.)+[a-z]{2,}$/i'],
            'port'   => ['sometimes', 'integer', 'between:1,65535'],
        ]);

        $port  = $validated['port']   ?? 443;
        $data  = $inspector->inspect($validated['domain'], $port);

        if (($data['error'] ?? false) === true) {
            return ApiResponse::error(
                null,
                'SSL inspection failed: ' . ($data['message'] ?? 'unknown error'),
                (array) 400,
                'SSL_INSPECTION_FAILED'
            );
        }

        return ApiResponse::success($data, 'SSL inspection successful');
    }
}
