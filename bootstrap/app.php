<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;

// Create the application instance
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware applied to all requests
        $middleware->append([
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            App\Http\Middleware\ExceptionHandlerMiddleware::class,
            \App\Http\Middleware\LogRequests::class,
        ]);

        // Define the "api" middleware group
        $middleware->group('api', [
            'throttle:60,1',
            \App\Http\Middleware\ForceJsonResponseMiddleware::class,
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\SanitizeInputMiddleware::class,
            \App\Http\Middleware\TransformMiddleware::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Group for routes requiring token and validation
        $middleware->group('auth.validate', [
            \App\Http\Middleware\EnsureApiKey::class,
        ]);
    })
    ->create();

// Register the custom exception handler
$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

return $app;
