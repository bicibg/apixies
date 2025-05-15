<?php

use App\Http\Controllers\Api\V1\EmailInspectorController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use App\Http\Controllers\Api\V1\SecurityHeadersInspectorController;
use App\Http\Controllers\Api\V1\SslHealthInspectorController;
use App\Http\Controllers\Api\V1\UserAgentInspectorController;
use App\Http\Controllers\HtmlToPdfController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

Route::apiV1(function () {
    // ==========================================
    // PUBLIC ENDPOINTS (no authentication needed)
    // ==========================================
    Route::get('health', HealthController::class)
        ->name('health')
        ->description('Perform liveness/health check.')
        ->requiredParams([])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);

    Route::get('ready', ReadinessController::class)
        ->name('ready')
        ->description('Perform readiness check (DB & cache).')
        ->requiredParams([])
        ->withoutMiddleware(\App\Http\Middleware\EnsureApiKey::class);


    // ==========================================
    // PROTECTED ENDPOINTS (require authentication)
    // ==========================================

    // All routes in this group require a valid API key
    Route::group([], function () {
        // ==========================================
        // TEST ENDPOINT (for API key testing)
        // ==========================================
        Route::get('test', function (Request $request) {
            // Return connection status without exposing IP
            return ApiResponse::success([
                'message' => 'API connection successful',
                'authenticated' => true,
                'timestamp' => now()->toIso8601String(),
            ], 'API test successful');
        })
            ->name('test')
            ->description('Test endpoint to verify API key authentication.')
            ->requiredParams([]);

        Route::get('inspect-email', EmailInspectorController::class)
            ->name('inspect-email')
            ->description('Inspect an email address for format, DNS/MX, disposable, role‐based, and suggestion.')
            ->requiredParams(['email']);

        Route::get('inspect-headers', SecurityHeadersInspectorController::class)
            ->name('inspect-headers')
            ->description('Inspect a website\'s HTTP response headers and grade security best‑practices.')
            ->requiredParams(['url']);

        Route::get('inspect-user-agent', UserAgentInspectorController::class)
            ->name('inspect-user-agent')
            ->description('Parse a User‑Agent string and detect browser, OS, device and bot flag.')
            ->requiredParams(['user_agent']);

        Route::get('inspect-ssl', SslHealthInspectorController::class)
            ->name('inspect-ssl')
            ->description('Inspect a domain\'s SSL certificate for validity, expiry and chain health.')
            ->requiredParams(['domain']);

        Route::post('html-to-pdf', HtmlToPdfController::class)
            ->name('html-to-pdf')
            ->withoutMiddleware([
                \App\Http\Middleware\ForceJsonResponseMiddleware::class,
                \App\Http\Middleware\TransformMiddleware::class
            ])
            ->description('Convert an HTML payload into a PDF file')
            ->requiredParams(['html']);
    });
});
