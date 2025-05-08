<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\ExceptionHandlerMiddleware;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\VerifyCsrfToken;           // ← import CSRF
use Illuminate\Session\Middleware\StartSession;     // ← needed by CSRF
use Illuminate\View\Middleware\ShareErrorsFromSession; // ← needed by CSRF

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:    __DIR__ . '/../routes/web.php',
        api:    __DIR__ . '/../routes/api.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $mw) {
        // Global middleware for every request (web + api)
        $mw->append([
            CorrelationId::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            ExceptionHandlerMiddleware::class,
            LogRequests::class,
        ]);

        // Define your "web" group (Blade pages, forms, etc.)
        $mw->group('web', [
            StartSession::class,           // session for CSRF
            ShareErrorsFromSession::class, // form errors
            VerifyCsrfToken::class,        // ← CSRF protection
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Define your "api" group (stateless JSON)
        $mw->group('api', [
            'throttle:100,1',
            \App\Http\Middleware\ForceJsonResponseMiddleware::class,
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\SanitizeInputMiddleware::class,
            \App\Http\Middleware\TransformMiddleware::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            CorrelationId::class,
            \App\Http\Middleware\EnsureApiKey::class,
            \App\Http\Middleware\SecureHeaders::class,
        ]);
    })
    ->create();

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

return $app;
