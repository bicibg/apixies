<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class DocsController extends Controller
{
    /**
     * Display the documentation overview page
     *
     * @return View
     */
    public function index(): View
    {
        $apiRoutes = config('api_examples');

        Log::debug('API Routes Keys: ' . implode(', ', array_keys($apiRoutes)));

        $categorizedRoutes = [];
        foreach ($apiRoutes as $key => $route) {
            $category = $route['category'] ?? 'general';
            if (!isset($categorizedRoutes[$category])) {
                $categorizedRoutes[$category] = [];
            }
            // Store with the original config key
            $categorizedRoutes[$category][$key] = $route;
        }

        return view('docs.index', [
            'activeSection' => 'overview',
            'categories' => $categorizedRoutes
        ]);
    }

    /**
     * Display the features documentation page
     *
     * @return View
     */
    public function features(): View
    {
        $categorizedRoutes = $this->getCategorizedRoutes();

        return view('docs.features', [
            'activeSection' => 'features',
            'categories' => $categorizedRoutes
        ]);
    }

    /**
     * Display the authentication documentation page
     *
     * @return View
     */
    public function authentication(): View
    {
        $categorizedRoutes = $this->getCategorizedRoutes();

        return view('docs.authentication', [
            'activeSection' => 'authentication',
            'categories' => $categorizedRoutes
        ]);
    }

    /**
     * Display all API endpoints
     *
     * @return View
     */
    public function endpoints(): View
    {
        $categorizedRoutes = $this->getCategorizedRoutes();

        Log::debug('Endpoint Categories: ' . implode(', ', array_keys($categorizedRoutes)));

        return view('docs.endpoints.index', [
            'categories' => $categorizedRoutes,
            'activeSection' => 'endpoints'
        ]);
    }

    /**
     * Display a specific API endpoint
     *
     * @param string $key
     * @return View|RedirectResponse
     */
    public function endpoint(string $key)
    {
        $apiRoutes = config('api_examples');

        Log::debug('Endpoint request for key: ' . $key);
        Log::debug('Available keys: ' . implode(', ', array_keys($apiRoutes)));

        // If numeric key is passed, redirect to the named key
        if (is_numeric($key)) {
            $routeKeys = array_keys($apiRoutes);
            if (isset($routeKeys[(int)$key])) {
                $namedKey = $routeKeys[(int)$key];
                Log::debug('Redirecting numeric key to named key: ' . $namedKey);
                return redirect()->route('docs.show', ['key' => $namedKey]);
            }
        }

        if (!isset($apiRoutes[$key])) {
            Log::warning('Endpoint not found: ' . $key);
            abort(404, 'Endpoint not found');
        }

        $apiRoute = $apiRoutes[$key];
        $category = $apiRoute['category'] ?? 'general';

        $categorizedRoutes = [];
        foreach ($apiRoutes as $routeKey => $route) {
            $routeCategory = $route['category'] ?? 'general';
            if (!isset($categorizedRoutes[$routeCategory])) {
                $categorizedRoutes[$routeCategory] = [];
            }
            // Store with the original config key
            $categorizedRoutes[$routeCategory][$routeKey] = $route;
        }

        return view('docs.show', [
            'apiRoute' => $apiRoute,
            'key' => $key,
            'activeSection' => 'endpoints',
            'activeCategory' => $category,
            'activeEndpoint' => $key,
            'categories' => $categorizedRoutes,
            'pageTitle' => $apiRoute['title'] ?? 'API Endpoint'
        ]);
    }

    /**
     * Display information about response formats
     *
     * @return View
     */
    public function responses(): View
    {
        $categorizedRoutes = $this->getCategorizedRoutes();

        return view('docs.responses', [
            'activeSection' => 'responses',
            'categories' => $categorizedRoutes
        ]);
    }

    /**
     * Display code examples for different platforms
     *
     * @return View
     */
    public function codeExamples(): View
    {
        $categorizedRoutes = $this->getCategorizedRoutes();

        return view('docs.code-examples', [
            'activeSection' => 'code-examples',
            'categories' => $categorizedRoutes
        ]);
    }

    /**
     * @return array
     */
    public function getCategorizedRoutes(): array
    {
        $apiRoutes = config('api_examples');

        $categorizedRoutes = [];
        foreach ($apiRoutes as $key => $route) {
            $category = $route['category'] ?? 'general';
            if (!isset($categorizedRoutes[$category])) {
                $categorizedRoutes[$category] = [];
            }
            // Store with the original config key
            $categorizedRoutes[$category][$key] = $route;
        }
        return $categorizedRoutes;
    }
}
