<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApiEndpointCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Process the request
        $response = $next($request);

        // Skip logging for non-API paths
        $path = $request->path();
        if (!str_starts_with($path, 'api/')) {
            return $response;
        }

        // Check if this is a sandbox request
        $isSandbox = $request->attributes->get('sandbox_mode', false);

        // Skip the test endpoint since it's handled by the TestController
        if ($path === 'api/v1/test') {
            return $response;
        }

        // Log the API call to the api_endpoint_counts table
        try {
            // Find existing count
            $existingCount = DB::table('api_endpoint_counts')
                ->where('endpoint', $path)
                ->where('is_sandbox', $isSandbox)
                ->first();

            if ($existingCount) {
                // Increment existing
                DB::table('api_endpoint_counts')
                    ->where('endpoint', $path)
                    ->where('is_sandbox', $isSandbox)
                    ->increment('count');
            } else {
                // Insert new
                DB::table('api_endpoint_counts')->insert([
                    'endpoint' => $path,
                    'count' => 1,
                    'is_sandbox' => $isSandbox,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error('Error incrementing API endpoint count: ' . $e->getMessage());
        }

        // Also log to the api_endpoint_logs table with more details
        try {
            // Extract user_id if authenticated
            $user_id = $request->user() ? $request->user()->id : null;
            $user_name = $request->user() ? $request->user()->name : null;

            // Get response status code
            $statusCode = $response->getStatusCode();

            // Insert log entry
            DB::table('api_endpoint_logs')->insert([
                'endpoint' => $path,
                'method' => $request->method(),
                'user_id' => $user_id,
                'user_name' => $user_name,
                'ip_address' => $request->ip(),
                'request_params' => json_encode($request->all()),
                'response_code' => $statusCode,
                'is_sandbox' => $isSandbox,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error('Error logging API endpoint: ' . $e->getMessage());
        }

        return $response;
    }
}
