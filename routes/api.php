<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use Illuminate\Support\Facades\Route;

Route::apiV1(function () {
    // ==========================================
    // PUBLIC ENDPOINTS (no authentication needed)
    // ==========================================

    // 1) Liveness — no params
    Route::get('health', HealthController::class)
        ->name('health')
        ->description('Perform liveness/health check.')
        ->requiredParams([])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);

    // 2) Readiness — no params
    Route::get('ready', ReadinessController::class)
        ->name('ready')
        ->description('Perform readiness check (DB & cache).')
        ->requiredParams([])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);

    // ==========================================
    // TEST ENDPOINT (for API key testing)
    // ==========================================

    // Simple endpoint to test API key authentication
    Route::get('test', function (\Illuminate\Http\Request $request) {
        return \App\Helpers\ApiResponse::success([
            'message' => 'API key is valid',
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ], 'Authentication successful');
    })
    ->name('api.test')
    ->description('Test endpoint to verify API key authentication.');

    // ==========================================
    // PROTECTED ENDPOINTS (require authentication)
    // ==========================================

    // All routes in this group require a valid API key
    Route::middleware('auth:sanctum')->group(function () {


    });
});
