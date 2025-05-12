<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ServiceInfoController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebApiKeyController;

Route::middleware('web')->group(function () {

    Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])
        ->name('docs.index');

    Route::get('/docs/{key}', [ServiceInfoController::class, 'showApiRoute'])
        ->where('key', '.*')             // <-- allow slashes in {key}
        ->name('docs.show');

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::get('/email/verify', fn() => view('auth.verify-email'))
        ->middleware('auth')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('docs.index');
    })
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.send');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{uuid}', [WebApiKeyController::class, 'destroy'])
            ->where('uuid', '[0-9a-fA-F\-]{36}')
            ->name('api-keys.destroy');
    });

    Route::get('suggestions', [SuggestionController::class, 'index']);
    Route::post('suggestions', [SuggestionController::class, 'store']);
    Route::post('suggestions/{suggestion}/vote', [SuggestionController::class, 'vote']);

    Route::get('/community-ideas', [SuggestionController::class, 'board'])
        ->name('suggestions.board');

    if (App::environment('local')) {
        Route::get('/generate-sitemap', [SitemapController::class, 'generate']);
    }

});
