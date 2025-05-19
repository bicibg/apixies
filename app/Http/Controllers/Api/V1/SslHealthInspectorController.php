<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\SslHealthInspectorService;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/v1/inspect-ssl",
 *     summary="SSL Health Inspector",
 *     description="Inspect SSL certificate details for a domain, including validity, expiry and chain health",
 *     operationId="inspectSsl",
 *     tags={"inspector"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="domain",
 *         in="query",
 *         description="Domain to inspect",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="example.com"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="SSL certificate inspected successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Inspection failed",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
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
