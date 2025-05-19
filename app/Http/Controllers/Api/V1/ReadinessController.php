<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Get(
 *     path="/api/v1/ready",
 *     summary="Readiness Check",
 *     description="Check if the API is ready to accept requests by verifying database and cache connections",
 *     operationId="readinessCheck",
 *     tags={"system"},
 *     @OA\Response(
 *         response=200,
 *         description="API is ready",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=503,
 *         description="API is not ready",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class ReadinessController extends Controller
{
    public function __invoke()
    {
        // DB check
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        // Cache check
        try {
            Cache::set('health_check', now(), 10);
            Cache::forget('health_check');
            $cacheOk = true;
        } catch (\Throwable $e) {
            $cacheOk = false;
        }

        if ($dbOk && $cacheOk) {
            return ApiResponse::success(
                ['status' => 'up'],
                'API is ready',
                'READY'
            );
        }

        return ApiResponse::error(
            'Service not ready',
            503,
            [],
            'NOT_READY'
        );
    }
}
