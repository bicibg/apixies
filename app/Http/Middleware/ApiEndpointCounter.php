<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

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

        // Check if this is a health/readiness endpoint
        $isHealthOrReadinessEndpoint = in_array($path, ['api/v1/health', 'api/v1/ready']);

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

            // Build log entry
            $logEntry = [
                'endpoint' => $path,
                'method' => $request->method(),
                'created_at' => now(),
                'updated_at' => now(),
                'response_code' => $response->getStatusCode(),
                'latency_ms' => $latencyMs,
                'sandbox' => $isSandbox,
            ];

            // Add request params if column exists
            if (in_array('request_params', $columns)) {
                try {
                    $logEntry['request_params'] = json_encode($request->all());
                } catch (\Exception $jsonError) {
                    $logEntry['request_params'] = '{"error":"Failed to encode request parameters"}';
                }
            }

            // Add sandbox token ID if it exists and we're in sandbox mode
            if ($isSandbox && $sandboxTokenId && in_array('sandbox_token_id', $columns)) {
                $logEntry['sandbox_token_id'] = $sandboxTokenId;
            }

            // Only record IP address for non-sandbox, non-health/ready calls
            // This helps differentiate between real API calls, sandbox calls, and health checks
            if (!$isSandbox && !$isHealthOrReadinessEndpoint && in_array('ip_address', $columns)) {
                $logEntry['ip_address'] = $request->ip();
            }

            // Only record user agent for non-sandbox calls
            if (!$isSandbox && in_array('user_agent', $columns)) {
                $logEntry['user_agent'] = $request->userAgent() ?? '';
            }

            // CRITICAL PART: Use Auth::guard('sanctum')->user() to match your EnsureApiKey middleware
            if (!$isHealthOrReadinessEndpoint && !$isSandbox) {
                // Get the authenticated user with the right guard
                $user = Auth::guard('sanctum')->user();

                // Debug log
                Log::debug("Sanctum user check: " . ($user ? "User found ID: {$user->id}" : "No user found"), [
                    'endpoint' => $path,
                    'has_bearer_token' => $request->bearerToken() ? true : false,
                    'has_api_key' => $request->header('X-API-Key') ? true : false,
                ]);

                if ($user) {
                    $logEntry['user_id'] = $user->id;

                    // Find the token used for authentication
                    $bearerToken = $request->bearerToken();
                    if ($bearerToken) {
                        $token = DB::table('personal_access_tokens')
                            ->where('tokenable_id', $user->id)
                            ->where('tokenable_type', get_class($user))
                            ->where('token', hash('sha256', $bearerToken))
                            ->first();

                        if ($token && in_array('api_key_id', $columns)) {
                            $logEntry['api_key_id'] = $token->id;
                        }
                    }
                } else {
                    // No authenticated user, but try to match API key
                    $apiKey = $request->header('X-API-Key');
                    if ($apiKey) {
                        // Try to manually find the user associated with this API key
                        // This is a fallback in case the authentication middleware didn't authenticate the user
                        $token = DB::table('personal_access_tokens')
                            ->where('name', 'api-key')
                            ->where('token', hash('sha256', $apiKey))
                            ->first();

                        if ($token) {
                            $logEntry['user_id'] = $token->tokenable_id;
                            if (in_array('api_key_id', $columns)) {
                                $logEntry['api_key_id'] = $token->id;
                            }

                            Log::debug("Found user ID from API key in personal_access_tokens table", [
                                'user_id' => $token->tokenable_id,
                                'api_key_id' => $token->id,
                            ]);
                        } else if (Schema::hasTable('api_keys')) {
                            // Try the custom api_keys table if it exists
                            $keyRecord = DB::table('api_keys')
                                ->where('key', $apiKey)
                                ->first();

                            if ($keyRecord) {
                                $logEntry['user_id'] = $keyRecord->user_id;
                                if (in_array('api_key_id', $columns)) {
                                    $logEntry['api_key_id'] = $keyRecord->id;
                                }

                                Log::debug("Found user ID from custom api_keys table", [
                                    'user_id' => $keyRecord->user_id,
                                    'api_key_id' => $keyRecord->id,
                                ]);
                            }
                        }
                    }
                }
            }

            // Filter entry to include only existing columns
            $filteredEntry = array_intersect_key($logEntry, array_flip($columns));

            // Only insert if we have data to insert
            if (!empty($filteredEntry)) {
                $logId = DB::table('api_endpoint_logs')->insertGetId($filteredEntry);

                // Debug log the full inserted entry
                Log::debug("Inserted API endpoint log entry", [
                    'log_id' => $logId,
                    'user_id' => $filteredEntry['user_id'] ?? null,
                    'api_key_id' => $filteredEntry['api_key_id'] ?? null,
                    'sandbox' => $filteredEntry['sandbox'] ?? null,
                    'sandbox_token_id' => $filteredEntry['sandbox_token_id'] ?? null,
                    'endpoint' => $path,
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error("Error logging API endpoint: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $response;
    }
}
