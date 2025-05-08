<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\ServiceInfoController;
use App\Http\Controllers\WebApiKeyController;
use App\Models\ApiEndpointCount;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function() {
    // Web routes
    Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])
        ->name('getApiRoutes');

    // Auth routes
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');

    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');

    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // API Keys - protected by auth middleware
    Route::middleware('auth')->group(function() {
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{id}', [WebApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    });

    Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');

    Route::get('/admin/api-stats', function () {
        $stats = \App\Models\ApiEndpointCount::orderByDesc('count')->get();
        return view('admin.api-stats', compact('stats'));
    })->middleware('auth', 'can:viewApiStats');

});
