<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\ServiceInfoController;
use App\Http\Controllers\WebApiKeyController;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function() {
    // Public landing
    Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])
        ->name('api-docs');

    // Registration
    Route::get('/register', [WebAuthController::class, 'showRegister'])
        ->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])
        ->name('register.submit');

    // Login
    Route::get('/login', [WebAuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])
        ->name('login.submit');

    // Logout
    Route::post('/logout', [WebAuthController::class, 'logout'])
        ->name('logout');

    // ──────────────────────────────────────────────────────────────────────────
    // Email Verification Routes
    // Show notice to verify email
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    // Handle the actual email verification
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/');  // or wherever you want verified users to land
    })->middleware(['auth', 'signed'])->name('verification.verify');

    // Resend the verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    // ──────────────────────────────────────────────────────────────────────────
    // Protected: only authenticated & verified users
    Route::middleware(['auth', 'verified'])->group(function() {
        // List your keys
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])
            ->name('api-keys.index');

        // Create a new key
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])
            ->name('api-keys.store');

        // Revoke by UUID
        Route::delete('/api-keys/{uuid}', [WebApiKeyController::class, 'destroy'])
            ->where('uuid', '[0-9a-fA-F\-]{36}')
            ->name('api-keys.destroy');
    });

    // ──────────────────────────────────────────────────────────────────────────
    // Admin: view API stats
    Route::middleware(['auth', 'can:viewApiStats'])->group(function () {
        Route::get('/admin/api-stats', function () {
            $stats = ApiEndpointCount::orderByDesc('count')->get();
            $logs  = ApiEndpointLog::orderByDesc('created_at')
                ->limit(100)
                ->get();

            return view('admin.api-stats', compact('stats', 'logs'));
        });
    });
});
