<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\ServiceInfoController;
use App\Http\Controllers\WebApiKeyController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function() {
    // Web routes
    Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])
        ->name('getApiRoutes');

    // Auth routes
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/auth/session', [WebAuthController::class, 'createSession'])->name('auth.session');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // API Keys - protected by auth middleware
    Route::middleware('auth')->group(function() {
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{id}', [WebApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    });
});
