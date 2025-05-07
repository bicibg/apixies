<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReadinessController
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
