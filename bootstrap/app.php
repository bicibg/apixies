<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\ExceptionHandlerMiddleware;
use App\Http\Middleware\LogRequests;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:    __DIR__ . '/../routes/web.php',
        api:    __DIR__ . '/../routes/api.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $mw) {
        // Global middleware for every request
        $mw->append([
            CorrelationId::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            ExceptionHandlerMiddleware::class,
            LogRequests::class,
        ]);

        // No more route-group definitions here
    })
    ->withExceptions(fn($exceptions) => null)
    ->create();

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

return $app;
