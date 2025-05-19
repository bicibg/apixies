<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\UserAgentInspectorService;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/v1/inspect-user-agent",
 *     summary="User Agent Inspector",
 *     description="Parse a User-Agent string to detect browser, operating system, device type and bot status",
 *     operationId="inspectUserAgent",
 *     tags={"inspector"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="user_agent",
 *         in="query",
 *         description="User-Agent string to parse",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User agent inspected successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class UserAgentInspectorController extends Controller
{
    public function __invoke(Request $request, UserAgentInspectorService $inspector)
    {
        $validated = $request->validate([
            'user_agent' => ['required', 'string', 'max:1024'],
        ]);

        $data = $inspector->inspect($validated['user_agent']);

        return ApiResponse::success($data, 'User‑Agent inspection successful');
    }
}
