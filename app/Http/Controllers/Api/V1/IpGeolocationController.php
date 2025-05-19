<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\IpGeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Get(
 *     path="/api/v1/ip-geolocation",
 *     summary="IP Geolocation",
 *     description="Convert IP addresses to location data including country, city, coordinates, timezone, and ISP information",
 *     operationId="ipGeolocation",
 *     tags={"inspector"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="ip",
 *         in="query",
 *         description="IP address to geolocate",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             example="8.8.8.8"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="IP geolocation retrieved successfully",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid IP address",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class IpGeolocationController extends Controller
{
    protected IpGeolocationService $ipGeolocationService;

    public function __construct(IpGeolocationService $ipGeolocationService)
    {
        $this->ipGeolocationService = $ipGeolocationService;
    }

    public function getGeolocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                400,
                $validator->errors()->toArray()
            );
        }

        $ip = $request->input('ip');

        if (!$this->ipGeolocationService->isValidIp($ip)) {
            return ApiResponse::error(
                'Invalid IP address',
                400,
                ['ip' => ['The provided IP address is not valid']]
            );
        }

        $geolocationData = $this->ipGeolocationService->getGeolocation($ip);

        if (isset($geolocationData['error'])) {
            return ApiResponse::error(
                $geolocationData['error'],
                500,
                ['ip' => ['Unable to retrieve geolocation data']]
            );
        }

        return ApiResponse::success(
            $geolocationData,
            'IP geolocation retrieved successfully'
        );
    }
}
