<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class ServiceInfoController extends Controller
{
    public function getApiRoutes(): Factory|Application|View|JsonResponse
    {
        $routes = $this->collectApiRoutes();

        if (request()->expectsJson()) {
            return response()->json(['status' => 200, 'data' => $routes]);
        }

        return view('docs.index', compact('routes'));
    }

    public function showApiRoute(string $key)
    {
        $routes = $this->collectApiRoutes();
        $route  = $routes->first(fn($r) => $r['uri'] === $key || "/{$r['uri']}" === $key);

        abort_unless($route, 404);
        return view('docs.show', compact('route'));
    }

    protected function collectApiRoutes()
    {
        return collect(Route::getRoutes())
            ->filter(fn($route) =>
                Str::startsWith($route->uri, 'api/v1/')
                && $route->getName()
                && ! Str::startsWith($route->getName(), 'generated::')
            )
            ->map(fn($route) => [
                'method'           => implode('|', $route->methods),
                'uri'              => $route->uri,
                'description'      => $route->action['description'] ?? 'No description provided',
                'route_params'     => $this->extractRouteParameters($route->uri),
                'query_params'     => $route->action['required_params'] ?? [],
                'example_response' => $this->getExample($route->uri),
            ])
            ->values();
    }

    private function extractRouteParameters(string $uri): array
    {
        preg_match_all('/\{(.+?)\}/', $uri, $m);
        return $m[1] ?? [];
    }

    private function getExample(string $uri): array
    {
        return Config::get("api_examples.{$uri}", [
            'status'    => 'success',
            'http_code' => 200,
            'code'      => strtoupper(str_replace(['/', '-', '.'], '_', $uri)) . '_OK',
            'message'   => 'Request successful',
            'data'      => new \stdClass(),
        ]);
    }
}
