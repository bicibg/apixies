<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

/**
 * @OA\Get(
 *     path="/api/v1/health",
 *     summary="Health Check",
 *     description="Check the health status of the API",
 *     operationId="healthCheck",
 *     tags={"system"},
 *     @OA\Response(
 *         response=200,
 *         description="API is healthy",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     )
 * )
 */
class HealthController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::success(
            ['status' => 'up'],
            'API is healthy.',
            'HEALTH_OK'
        );
    }
}
