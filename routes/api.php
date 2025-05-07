<?php
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::prefix('v1')
    ->middleware('api')
    ->group(function () {
        // Health check endpoint
        Route::get('health', HealthController::class)
            ->name('health')
            ->description('Perform a liveness/health check of the API')
            ->requiredParams([]);
    });
