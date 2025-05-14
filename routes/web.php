<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\{
    DocsController,
    WebAuthController,
    WebApiKeyController,
    SandboxTokenController,
    SuggestionController
};

// Redirect home to docs
Route::redirect('/', '/docs')->name('home');

// ─────────────────────────────────────────────────────────────────────────────
// Documentation
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/',       [DocsController::class, 'index'])->name('index');
    Route::get('features',[DocsController::class, 'features'])->name('features');
    Route::get('authentication',[DocsController::class, 'authentication'])->name('authentication');
    Route::get('responses',[DocsController::class, 'responses'])->name('responses');
    Route::get('examples',[DocsController::class, 'examples'])->name('examples');

    // Endpoints docs
    Route::get('endpoints',            [DocsController::class, 'endpoints'])->name('endpoints.index');
    Route::get('endpoints/{key}',      [DocsController::class, 'showEndpoint'])->name('endpoints.show');
});

// ─────────────────────────────────────────────────────────────────────────────
// Authentication
// ─────────────────────────────────────────────────────────────────────────────
Route::controller(WebAuthController::class)->group(function () {
    Route::get('register',  'showRegister')->name('register');
    Route::post('register', 'register')->name('register.submit');
    Route::get('login',     'showLogin')->name('login');
    Route::post('login',    'login')->name('login.submit');
    Route::post('logout',   'logout')->name('logout');
});

// Email verification
Route::view('email/verify', 'auth.verify-email')
    ->middleware('auth')
    ->name('verification.notice');

Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('docs.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ─────────────────────────────────────────────────────────────────────────────
// API Keys (authenticated & verified)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('api-keys', WebApiKeyController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['api-keys' => 'uuid']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Suggestions & Community Board
// ─────────────────────────────────────────────────────────────────────────────
Route::resource('suggestions', SuggestionController::class)
    ->only(['index', 'store']);

Route::post('suggestions/{suggestion}/vote', [SuggestionController::class, 'vote'])
    ->name('suggestions.vote');

Route::get('community-ideas', [SuggestionController::class, 'board'])
    ->name('suggestions.board');

// ─────────────────────────────────────────────────────────────────────────────
// Sandbox Token Management
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('sandbox/token')
    ->controller(SandboxTokenController::class)
    ->group(function () {
        Route::get('/',         'issue');
        Route::post('create',   'create');
        Route::post('refresh',  'refresh');
        Route::post('validate', 'validate');
    });

// ─────────────────────────────────────────────────────────────────────────────
// PDF Preview
// ─────────────────────────────────────────────────────────────────────────────
Route::get('pdf/preview', function (Request $request) {
    $html = base64_decode($request->query('html', ''));
    return view('docs.pdf.preview', ['content' => $html]);
})->name('pdf.preview');
