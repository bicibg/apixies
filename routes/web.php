<?php

use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ServiceInfoController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebApiKeyController;
use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;

Route::middleware('web')->group(function () {

    // Main docs landing (lists all endpoints + sections)
    Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])
        ->name('docs.index');

    Route::get('/docs/{key}', [ServiceInfoController::class, 'showApiRoute'])
        ->where('key', '.*')             // <-- allow slashes in {key}
        ->name('docs.show');


    // Auth
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/email/verify', fn() => view('auth.verify-email'))
        ->middleware('auth')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', fn(EmailVerificationRequest $r) => $r->fulfill() ? redirect()->route('docs.index') : null)
        ->middleware(['auth','signed'])->name('verification.verify');
    Route::post('/email/verification-notification', fn(Request $r) => $r->user()->sendEmailVerificationNotification()
        ? back()->with('status','verification-link-sent') : null)
        ->middleware(['auth','throttle:6,1'])->name('verification.send');

    // API Key management
    Route::middleware(['auth','verified'])->group(function () {
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{uuid}', [WebApiKeyController::class, 'destroy'])
            ->where('uuid','[0-9a-fA-F\-]{36}')
            ->name('api-keys.destroy');
    });

    // Admin stats
//    Route::middleware(['auth','can:viewApiStats'])->get('/admin/api-stats', function() {
//        $stats = ApiEndpointCount::orderByDesc('count')->get();
//        $logs  = ApiEndpointLog::orderByDesc('created_at')->limit(100)->get();
//        return view('admin.api-stats', compact('stats','logs'));
//    })->name('admin.api-stats');

    Route::get('suggestions', [SuggestionController::class, 'index']);
    Route::post('suggestions', [SuggestionController::class, 'store']);
    Route::post('suggestions/{suggestion}/vote', [SuggestionController::class, 'vote']);
});
