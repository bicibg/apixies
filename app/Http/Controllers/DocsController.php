<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiEndpointCount;
use Illuminate\Support\Facades\DB;

class DocsController extends Controller
{
    /**
     * Display the documentation landing page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get popular endpoints if available
        try {
            $popularEndpoints = ApiEndpointCount::select('endpoint', DB::raw('sum(count) as total'))
                ->groupBy('endpoint')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $popularEndpoints = collect();
        }

        return view('docs.index', [
            'popularEndpoints' => $popularEndpoints,
        ]);
    }

    /**
     * Display the features documentation
     *
     * @return \Illuminate\View\View
     */
    public function features()
    {
        return view('docs.features');
    }

    /**
     * Display the authentication documentation
     *
     * @return \Illuminate\View\View
     */
    public function authentication()
    {
        return view('docs.authentication');
    }

    /**
     * Display the response format documentation
     *
     * @return \Illuminate\View\View
     */
    public function responses()
    {
        return view('docs.responses');
    }

    /**
     * Display the code examples documentation
     *
     * @return \Illuminate\View\View
     */
    public function examples()
    {
        return view('docs.examples');
    }

    /**
     * Display the list of API endpoints
     *
     * @return \Illuminate\View\View
     */
    public function endpoints()
    {
        $apiRoutes = config('api_examples');
        $categories = collect($apiRoutes)->groupBy('category');

        return view('docs.endpoints.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display a specific API endpoint
     *
     * @param string $key
     * @return \Illuminate\View\View
     */
    public function showEndpoint($key)
    {
        $apiRoutes = config('api_examples');

        if (!isset($apiRoutes[$key])) {
            abort(404);
        }

        return view('docs.endpoints.show', [
            'apiRoute' => $apiRoutes[$key],
            'key' => $key,
            'popularEndpoints' => collect(),
        ]);
    }
}
