<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

class ServiceInfoController extends Controller
{
    /**
     * Retrieve and display all API v1 routes for the service.
     *
     * @return Factory|Application|View|JsonResponse
     */
    public function getApiRoutes(): Factory|Application|View|JsonResponse
    {
        // Get every registered route
        $allRoutes = Route::getRoutes();

        // Filter to only those whose URI begins with api/v1/
        $apiRoutes = collect($allRoutes)
            ->filter(fn($route) =>
                str_starts_with($route->uri, 'api/v1/') &&            // look for api/v1/
                $route->getName() &&                                   // keep only named routes
                ! str_starts_with($route->getName(), 'generated::')    // drop autogenerated ones
            )
            ->map(fn($route) => [
                'method'            => implode('|', $route->methods),
                'uri'               => $route->uri,
                'description'       => $route->action['description'] ?? 'No description provided',
                'route_params'      => $this->extractRouteParameters($route->uri),
                'query_params'      => $route->action['required_params'] ?? [],
                // attach a canned example response per‐endpoint:
                'example_response'  => $this->getExampleFor($route->uri),
            ])
            ->values();  // reset the keys

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 200,
                'data'   => $apiRoutes,
            ]);
        }

        return view('docs.index', [
            'routes' => $apiRoutes,
        ]);
    }

    /**
     * Show details for a single API route.
     */
    public function showApiRoute(string $key)
    {
        // reuse the same collection logic
        $allRoutes = Route::getRoutes();
        $routes = collect($allRoutes)
            ->filter(fn($route) =>
                str_starts_with($route->uri, 'api/v1/') &&
                $route->getName() &&
                ! str_starts_with($route->getName(), 'generated::')
            )
            ->map(fn($route) => [
                'method'            => implode('|', $route->methods),
                'uri'               => $route->uri,
                'description'       => $route->action['description'] ?? 'No description provided',
                'route_params'      => $this->extractRouteParameters($route->uri),
                'query_params'      => $route->action['required_params'] ?? [],
                'example_response'  => $this->getExampleFor($route->uri),
            ])
            ->values();

        // find by URI segment
        $route = $routes->first(fn($r) => $r['uri'] === $key || "/{$r['uri']}" === $key);
        if (! $route) {
            abort(404);
        }

        return view('docs.show', compact('route'));
    }

    /**
     * Extract {parameters} from a URI string.
     */
    private function extractRouteParameters(string $uri): array
    {
        preg_match_all('/\{(.+?)\}/', $uri, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Provide a canned example response array for each endpoint.
     */
    private function getExampleFor(string $uri): array
    {
        return match ($uri) {
            'api/v1/inspect-email' => [
                "status"        => "success",
                "http_code"     => 200,
                "code"          => "200",
                "message"       => "Email inspection successful",
                "data"          => [
                    "email"             => "someone@exmaple.com",
                    "format_valid"      => true,
                    "domain_resolvable" => false,
                    "mx_records_found"  => false,
                    "mailbox_exists"    => false,
                    "is_disposable"     => false,
                    "is_role_based"     => false,
                    "suggestion"        => "someone@example.com"
                ],
            ],
            default => [
                "status"    => "success",
                "http_code" => 200,
                "code"      => strtoupper(str_replace(['/', '-', '.'], '_', $uri)) . "_OK",
                "message"   => "Request successful",
                "data"      => new \stdClass(),
            ],
        };
    }
}
