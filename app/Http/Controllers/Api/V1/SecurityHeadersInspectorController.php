<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SecurityHeadersInspectorService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/v1/inspect-headers",
 *     summary="Security Headers Inspector",
 *     description="Inspect security headers for a URL to check for adherence to current best practices",
 *     operationId="inspectSecurityHeaders",
 *     tags={"inspector"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="url",
 *         in="query",
 *         description="URL to inspect",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="https://example.com"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Security headers inspected successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class SecurityHeadersInspectorController extends Controller
{
    public function __invoke(Request $request, SecurityHeadersInspectorService $inspector)
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:255'],
        ]);

        $raw = trim($validated['url']);
        if (!preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . ltrim($raw, '/');
        }

        if (!filter_var($raw, FILTER_VALIDATE_URL)) {
            return ApiResponse::error(
                'The url field must be a valid URL.',
                422,
                ['INVALID_URL']
            );
        }

        $selfDomains = [
            'apixies.io',
            parse_url(config('app.url'), PHP_URL_HOST),
            $_SERVER['HTTP_HOST'] ?? '',
            $_SERVER['SERVER_NAME'] ?? '',
        ];

        $urlHost = parse_url($raw, PHP_URL_HOST);

        if ($urlHost && in_array($urlHost, array_filter($selfDomains))) {
            return ApiResponse::error(
                'Cannot scan own domain for security reasons',
                400,
                ['SELF_REFERENCE_NOT_ALLOWED']
            );
        }

        $result = $inspector->inspect($raw);

        if (($result['error'] ?? false) === true) {
            // Use the error message from the service if available
            $errorMessage = $result['message'] ?? 'Unable to fetch headers for the given URL.';

            return ApiResponse::error(
                $errorMessage,
                400,
                ['SECURITY_HEADERS_INSPECTION_FAILED']
            );
        }

        return ApiResponse::success(
            $result,
            'Security headers inspected successfully.'
        );
    }
}
