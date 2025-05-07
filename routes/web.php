<?php

use App\Http\Controllers\ServiceInfoController;
use Illuminate\Support\Facades\Route;
use Prometheus\CollectorRegistry, Prometheus\RenderTextFormat;

Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])->name('getApiRoutes');

Route::prefix('api')
    ->middleware('api')
    ->group(base_path('routes/api.php'));
