<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Apixies.io API Documentation",
 *      description="API documentation for Apixies.io - A collection of utility APIs for developers",
 *      @OA\Contact(
 *          email="support@apixies.io",
 *          name="Apixies.io Support"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\Server(
 *      url="https://apixies.io",
 *      description="Production API Server"
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Development API Server"
 * )
 *
 * @OA\Tag(
 *     name="system",
 *     description="System endpoints for health and readiness checks"
 * )
 *
 * @OA\Tag(
 *     name="inspector",
 *     description="Inspector endpoints for analyzing various resources"
 * )
 *
 * @OA\Tag(
 *     name="converter",
 *     description="Converter endpoints for transforming data formats"
 * )
 *
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     securityScheme="X-API-KEY",
 *     name="X-API-KEY",
 *     description="API Key for authentication"
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="success"
 *     ),
 *     @OA\Property(
 *         property="http_code",
 *         type="integer",
 *         example=200
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         example="SUCCESS"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Operation completed successfully"
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *     @OA\Property(
 *         property="http_code",
 *         type="integer",
 *         example=400
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         example="VALIDATION_ERROR"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="The request data is invalid"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object"
 *     )
 * )
 */
class ApiDocController extends Controller
{
    // This controller is just for OpenAPI annotations
}
