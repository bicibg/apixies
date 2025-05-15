<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // Store start time to calculate request latency
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Calculate latency in milliseconds
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        // Skip logging for non-API paths
        $path = $request->path();
        if (!str_starts_with($path, 'api/')) {
            return $response;
        }

        // IMPORTANT: Explicitly check if sandbox_mode is set, default to false if not set
        $isSandbox = (bool) $request->attributes->get('sandbox_mode', false);

        // Get sandbox token ID if available
        $sandboxTokenId = null;
        if ($isSandbox) {
            $sandboxToken = $request->header('X-Sandbox-Token');
            if ($sandboxToken) {
                try {
                    $token = DB::table('sandbox_tokens')
                        ->where('token', $sandboxToken)
                        ->first();

                    if ($token && isset($token->id)) {
                        $sandboxTokenId = $token->id;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get sandbox token ID: " . $e->getMessage());
                }
            }
        }

        // Log the sandbox status for debugging
        Log::debug("ApiEndpointCounter processing path: {$path}, sandbox_mode: " . ($isSandbox ? 'true' : 'false'));

        // Log the API call to the api_endpoint_counts table
        try {
            // Check if table exists and has required columns
            if (!Schema::hasTable('api_endpoint_counts')) {
                Log::error("api_endpoint_counts table doesn't exist");
                return $response;
            }

            // Use upsert to handle inserts and updates gracefully (Laravel 8+)
            $now = now();
            DB::table('api_endpoint_counts')->upsert(
                [
                    'endpoint' => $path,
                    'is_sandbox' => $isSandbox,
                    'count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ],
                ['endpoint', 'is_sandbox'], // Keys that define uniqueness
                ['count' => DB::raw('count + 1'), 'updated_at' => $now] // Columns to update if record exists
            );

            Log::debug("Upserted count for {$path}, sandbox=" . ($isSandbox ? 'true' : 'false'));

        } catch (\Exception $e) {
            // Try a fallback approach if upsert fails
            try {
                // First try to increment
                $affected = DB::table('api_endpoint_counts')
                    ->where('endpoint', $path)
                    ->where('is_sandbox', $isSandbox)
                    ->increment('count');

                // If no record was updated, try to insert
                if ($affected === 0) {
                    DB::table('api_endpoint_counts')->insert([
                        'endpoint' => $path,
                        'count' => 1,
                        'is_sandbox' => $isSandbox,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                Log::debug("Used fallback method to count for {$path}, sandbox=" . ($isSandbox ? 'true' : 'false'));
            } catch (\Exception $fallbackError) {
                // Log the error but don't disrupt the response
                Log::error("Error incrementing API endpoint count (both methods failed): " . $fallbackError->getMessage());
            }
        }

        // Also log to the api_endpoint_logs table with more details
        try {
            // Check if table exists
            if (!Schema::hasTable('api_endpoint_logs')) {
                Log::error("api_endpoint_logs table doesn't exist");
                return $response;
            }

            // Get schema information for logs table
            $columns = Schema::getColumnListing('api_endpoint_logs');

            // Build log entry based on available columns
            $logEntry = [];

            // Always include these core fields
            if (in_array('endpoint', $columns)) {
                $logEntry['endpoint'] = $path;
            }

            if (in_array('method', $columns)) {
                $logEntry['method'] = $request->method();
            }

            if (in_array('created_at', $columns)) {
                $logEntry['created_at'] = now();
            }

            if (in_array('updated_at', $columns)) {
                $logEntry['updated_at'] = now();
            }

            // Add conditional fields
            if (in_array('response_code', $columns)) {
                $logEntry['response_code'] = $response->getStatusCode();
            }

            if (in_array('user_id', $columns) && $request->user()) {
                $logEntry['user_id'] = $request->user()->id;
            }

            if (in_array('user_name', $columns) && $request->user()) {
                $logEntry['user_name'] = $request->user()->name;
            }

            if (in_array('ip_address', $columns)) {
                $logEntry['ip_address'] = $request->ip();
            }

            if (in_array('request_params', $columns)) {
                // Safely encode request parameters
                try {
                    $logEntry['request_params'] = json_encode($request->all());
                } catch (\Exception $jsonError) {
                    $logEntry['request_params'] = '{"error":"Failed to encode request parameters"}';
                    Log::warning("Failed to encode request parameters for log: " . $jsonError->getMessage());
                }
            }

            if (in_array('is_sandbox', $columns)) {
                $logEntry['is_sandbox'] = $isSandbox;
            }

            // Add sandbox token ID if available
            if (in_array('sandbox_token_id', $columns) && $sandboxTokenId) {
                $logEntry['sandbox_token_id'] = $sandboxTokenId;
            }

            // IMPORTANT: Add latency_ms field that's required
            if (in_array('latency_ms', $columns)) {
                $logEntry['latency_ms'] = $latencyMs;
            }

            // Only insert if we have data to insert
            if (!empty($logEntry)) {
                $logId = DB::table('api_endpoint_logs')->insertGetId($logEntry);
                Log::debug("Inserted log entry (ID: {$logId}) for {$path}, sandbox=" . ($isSandbox ? 'true' : 'false'));
            } else {
                Log::warning("No valid columns found for api_endpoint_logs table");
            }

        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error("Error logging API endpoint: " . $e->getMessage());
        }

        return $response;
    }
}
