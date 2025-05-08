<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use App\Http\Controllers\Api\V1\TestController;
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

    // 3) User login
    Route::post('login', [AuthController::class, 'login'])
        ->name('login')
        ->description('Authenticate an existing user and return an API token.')
        ->requiredParams([
            'email',
            'password'
        ])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);

    // 4) Request password reset link
    Route::post('password/forgot', [PasswordResetController::class, 'sendLink'])
        ->name('password.forgot')
        ->description('Send a password reset link to the given email address.')
        ->requiredParams([
            'email'
        ])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);

    // 5) Reset password
    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->name('password.reset')
        ->description('Reset a user\'s password using the token emailed to them.')
        ->requiredParams([
            'email',
            'token',
            'password',
            'password_confirmation'
        ])
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
        // Logout
        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout')
            ->description('Revoke the current user\'s access token.')
            ->requiredParams([]);

        // List API keys
        Route::get('api-keys', [ApiKeyController::class, 'index'])
            ->name('api-keys.index')
            ->description('List all API tokens for the authenticated user.')
            ->requiredParams([]);

        // Create a new API key
        Route::post('api-keys', [ApiKeyController::class, 'store'])
            ->name('api-keys.store')
            ->description('Generate a new API token with a given name (and optional abilities).')
            ->requiredParams([
                'name',       // the friendly name for this token
                // 'abilities' // optional array of abilities
            ]);

        // Revoke an API key
        Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])
            ->name('api-keys.destroy')
            ->description('Revoke (delete) the API token identified by {id}.')
            ->requiredParams([]); // {id} is a route param, not in the body

    });
});
