<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\SandboxTokenController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebApiKeyController;

Route::middleware('web')->group(function () {
    // Documentation Routes - SPECIFIC ROUTES FIRST
    Route::get('/docs/features', [DocsController::class, 'features'])->name('docs.features');
    Route::get('/docs/authentication', [DocsController::class, 'authentication'])->name('docs.authentication');
    Route::get('/docs/endpoints', [DocsController::class, 'endpoints'])->name('docs.endpoints.index');
    Route::get('/docs/responses', [DocsController::class, 'responses'])->name('docs.responses');
    Route::get('/docs/code-examples', [DocsController::class, 'codeExamples'])->name('docs.code-examples');

    // API endpoint documentation
    Route::get('/docs/{key}', [DocsController::class, 'endpoint'])
        ->name('docs.show')
        ->where('key', '[a-zA-Z0-9\-]+'); // Only allow alphanumeric keys

    // Homepage - index route
    Route::get('/', [DocsController::class, 'index'])->name('docs.index');

    // Authentication Routes
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Email Verification Routes
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

    // API Key Management
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/api-keys', [WebApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [WebApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{uuid}', [WebApiKeyController::class, 'destroy'])
            ->where('uuid', '[0-9a-fA-F\-]{36}')
            ->name('api-keys.destroy');
    });

    // Suggestions
    Route::get('suggestions', [SuggestionController::class, 'index']);
    Route::post('suggestions', [SuggestionController::class, 'store']);
    Route::post('suggestions/{suggestion}/vote', [SuggestionController::class, 'vote']);
    Route::get('/community-ideas', [SuggestionController::class, 'board'])
        ->name('suggestions.board');

    // Sandbox Token Management
    Route::post('/sandbox/token/create', [SandboxTokenController::class, 'create']);
    Route::post('/sandbox/token/refresh', [SandboxTokenController::class, 'refresh']);
    Route::post('/sandbox/token/validate', [SandboxTokenController::class, 'validateToken']); // Updated method name

    // PDF Preview
    Route::get('/pdf/preview', function (Illuminate\Http\Request $request) {
        $html = base64_decode($request->query('html', ''));
        return view('docs.pdf.preview', ['content' => $html]);
    })->name('pdf.preview');
});
