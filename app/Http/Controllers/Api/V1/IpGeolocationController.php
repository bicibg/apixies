<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\IpGeolocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IpGeolocationController extends Controller
{
    protected IpGeolocationService $ipGeolocationService;

    /**
     * Create a new controller instance.
     *
     * @param IpGeolocationService $ipGeolocationService
     */
    public function __construct(IpGeolocationService $ipGeolocationService)
    {
        $this->ipGeolocationService = $ipGeolocationService;
    }

    /**
     * Get geolocation information for an IP address.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getGeolocation(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'ip' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                400,
                $validator->errors(),
            );
        }

        $ip = $request->input('ip');

        // Check if the IP is valid
        if (!$this->ipGeolocationService->isValidIp($ip)) {
            return ApiResponse::error(
                'Invalid IP address',
                400,
                ['ip' => 'The provided IP address is not valid'],
            );
        }

        // Get geolocation data
        $geolocationData = $this->ipGeolocationService->getGeolocation($ip);

        // Check if there was an error
        if (isset($geolocationData['error'])) {
            return ApiResponse::error(
                $geolocationData['error'],
                500,
                ['ip' => 'Unable to retrieve geolocation data'],
            );
        }

        return ApiResponse::success(
            $geolocationData,
            'IP geolocation retrieved successfully'
        );
    }
}
