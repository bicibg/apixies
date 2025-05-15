<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\ExceptionHandlerMiddleware;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\SandboxTokenUsageMiddleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $mw) {
        // Global middleware - keep minimal
        $mw->append([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $mw->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,   // optional but recommended
        ]);


        // Set up auth middleware
        $mw->alias(['auth' => \App\Http\Middleware\Authenticate::class]);

        // API middleware group
        $mw->group('api', [
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            'throttle:100,1',
            \App\Http\Middleware\ForceJsonResponseMiddleware::class,
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\SanitizeInputMiddleware::class,
            \App\Http\Middleware\TransformMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureApiKey::class,
            \App\Http\Middleware\SecureHeaders::class,
            \App\Http\Middleware\ApiEndpointCounter::class,
            CorrelationId::class,
            ExceptionHandlerMiddleware::class,
            LogRequests::class,
            SandboxTokenUsageMiddleware::class, // Add the new middleware here
        ]);
    })
    ->create();

// Add essential auth services
$app->singleton(ExceptionHandler::class, Handler::class);
$app->register(\Illuminate\Auth\AuthServiceProvider::class);
$app->register(\Illuminate\Session\SessionServiceProvider::class);

return $app;
