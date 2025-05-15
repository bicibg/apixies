<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.index', [
            'activeSection' => 'overview',
            'categories' => $categories
        ]);
    }

    /**
     * Display the features documentation page
     *
     * @return View
     */
    public function features(): View
    {
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.features', [
            'activeSection' => 'features',
            'categories' => $categories
        ]);
    }

    /**
     * Display the authentication documentation page
     *
     * @return View
     */
    public function authentication(): View
    {
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.authentication', [
            'activeSection' => 'authentication',
            'categories' => $categories
        ]);
    }

    /**
     * Display all API endpoints
     *
     * @return View
     */
    public function endpoints(): View
    {
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.endpoints.index', [
            'categories' => $categories,
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

        // Handle numeric keys if they still exist in URLs
        if (is_numeric($key)) {
            // Convert to array to get keys for numeric access
            $routes = $apiRoutes;
            $keys = array_keys($routes);

            if (isset($keys[(int)$key])) {
                // Redirect to the named route using the string key
                return redirect()->route('docs.show', ['key' => $keys[(int)$key]]);
            }
        }

        if (!isset($apiRoutes[$key])) {
            abort(404, 'Endpoint not found');
        }

        $apiRoute = $apiRoutes[$key];
        $category = $apiRoute['category'] ?? 'general';
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.show', [
            'apiRoute' => $apiRoute,
            'key' => $key,
            'activeSection' => 'endpoints',
            'activeCategory' => $category,
            'activeEndpoint' => $key,
            'categories' => $categories,
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
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.responses', [
            'activeSection' => 'responses',
            'categories' => $categories
        ]);
    }

    /**
     * Display code examples for different platforms
     *
     * @return View
     */
    public function codeExamples(): View
    {
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.code-examples', [
            'activeSection' => 'code-examples',
            'categories' => $categories
        ]);
    }
}
