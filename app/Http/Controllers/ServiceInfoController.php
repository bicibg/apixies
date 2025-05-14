<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiEndpointCount;
use Illuminate\Support\Facades\DB;

class ServiceInfoController extends Controller
{
    /**
     * Get API routes for the documentation
     *
     * @return \Illuminate\View\View
     */
    public function getApiRoutes()
    {
        // Get API routes from config
        $apiRoutes = config('api_examples');

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
            'apiRoutes' => $apiRoutes,
            'popularEndpoints' => $popularEndpoints,
        ]);
    }

    /**
     * Show a specific API route
     *
     * @param string $key
     * @return \Illuminate\View\View
     */
    public function showApiRoute($key)
    {
        // Get API routes from config
        $apiRoutes = config('api_examples');

        if (!isset($apiRoutes[$key])) {
            abort(404);
        }

        return view('docs.show', [
            'apiRoute' => $apiRoutes[$key],
            'key' => $key,
        ]);
    }
}
