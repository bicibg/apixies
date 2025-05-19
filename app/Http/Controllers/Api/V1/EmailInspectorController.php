<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmailInspectorService;
use App\Helpers\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Get(
 *     path="/api/v1/inspect-email",
 *     summary="Email Inspector",
 *     description="Inspect email address details including format validation, MX record checks, and disposable email detection",
 *     operationId="inspectEmail",
 *     tags={"inspector"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         description="Email address to inspect",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             format="email",
 *             example="user@example.com"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email inspection successful",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class EmailInspectorController extends Controller
{
    protected EmailInspectorService $inspector;

    public function __construct(EmailInspectorService $inspector)
    {
        $this->inspector = $inspector;
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
        ]);

        $result = $this->inspector->inspect($request->input('email'));

        return ApiResponse::success(
            $result,
            'Email inspection successful',
            Response::HTTP_OK
        );
    }
}
