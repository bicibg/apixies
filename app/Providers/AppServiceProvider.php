<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// 1a) Facade for static macros (apiV1)
use Illuminate\Support\Facades\Route as RouteFacade;

// 1b) The Route _object_ class for instance macros (description, requiredParams)
use Illuminate\Routing\Route as RouteObject;

use Spatie\ResponseCache\Middlewares\CacheResponse;
use App\Http\Middleware\ForceJsonResponseMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\SanitizeInputMiddleware;
use App\Http\Middleware\TransformMiddleware;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\EnsureApiKey;
use App\Http\Middleware\SecureHeaders;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
        // A) Instance macros on the Route _object_
        //
        RouteObject::macro('description', function (string $description) {
            // $this is the Route instance
            $this->action['description'] = ($this->action['description'] ?? '') . $description;
            return $this;
        });

        RouteObject::macro('requiredParams', function (array $params) {
            $this->action['required_params'] = $params;
            return $this;
        });

        //
        // B) Static macro on the Route facade for your /api/v1 stack
        //
        RouteFacade::macro('apiV1', function (callable $callback) {
            // RouteFacade proxies to the router under the hood
            return RouteFacade::prefix('v1')
                ->middleware([
                    'throttle:100,1',
                    CacheResponse::class,
                    ForceJsonResponseMiddleware::class,
                    CorsMiddleware::class,
                    SanitizeInputMiddleware::class,
                    TransformMiddleware::class,
                    ValidatePostSize::class,
                    SubstituteBindings::class,
                    CorrelationId::class,
                    EnsureApiKey::class,
                    SecureHeaders::class,
                ])
                ->group($callback);
        });
    }
}
