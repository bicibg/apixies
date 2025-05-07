<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;

class HealthController
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
