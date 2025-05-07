<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;

Route::apiV1(function () {
    Route::get('health', HealthController::class)
        ->name('health')
        ->description('Perform liveness/health check.')
        ->requiredParams([]);

    Route::get('ready', ReadinessController::class)
        ->name('ready')
        ->description('Perform readiness check (DB & cache).')
        ->requiredParams([]);
});
