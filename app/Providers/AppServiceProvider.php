<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Routing\Route as RouteObject;
use Laravel\Sanctum\Sanctum;
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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RouteObject::macro('description', function (string $description) {
            // $this is the Route instance
            $this->action['description'] = ($this->action['description'] ?? '') . $description;
            return $this;
        });

        RouteObject::macro('requiredParams', function (array $params) {
            $this->action['required_params'] = $params;
            return $this;
        });

        RouteFacade::macro('apiV1', function (callable $callback) {
            // RouteFacade proxies to the router under the hood
            return RouteFacade::prefix('v1')
                ->middleware([
                    ForceJsonResponseMiddleware::class,
                    CorsMiddleware::class,
                    SanitizeInputMiddleware::class,
                    TransformMiddleware::class,
                    ValidatePostSize::class,
                    SubstituteBindings::class,
                    CorrelationId::class,
                    EnsureApiKey::class,
                    SecureHeaders::class,
                    'throttle:100,1', // 100 requests per minute
                ])
                ->group($callback);
        });
    }
}
