<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

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

        // Store the token ID before processing the request
        // This is important because Sanctum removes the token info after auth
        $tokenId = null;
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            // Get the current token ID directly from Sanctum
            // This approach works because Sanctum already identified the token
            $token = $request->bearerToken();
            if ($token) {
                try {
                    // Find the token directly from the Sanctum model
                    $accessToken = PersonalAccessToken::findToken($token);
                    if ($accessToken) {
                        $tokenId = $accessToken->id;
                        Log::debug("Found token ID: {$tokenId} for user {$user->id}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Error finding token: " . $e->getMessage());
                }
            }
        }

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

        // Get sandbox token ID if available
        $sandboxTokenId = null;
        $isSandboxRequest = false;

        $sandboxToken = $request->header('X-Sandbox-Token');
        if ($sandboxToken) {
            $isSandboxRequest = true;
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
                    'is_sandbox' => $isSandboxRequest,
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
                    ->where('is_sandbox', $isSandboxRequest)
                    ->increment('count');

                // If no record was updated, try to insert
                if ($affected === 0) {
                    DB::table('api_endpoint_counts')->insert([
                        'endpoint' => $path,
                        'count' => 1,
                        'is_sandbox' => $isSandboxRequest,
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
            ];

            // Add request params if column exists
            if (in_array('request_params', $columns)) {
                try {
                    $logEntry['request_params'] = json_encode($request->all());
                } catch (\Exception $jsonError) {
                    $logEntry['request_params'] = '{"error":"Failed to encode request parameters"}';
                }
            }

            // Add sandbox token ID if available
            if ($sandboxTokenId && in_array('sandbox_token_id', $columns)) {
                $logEntry['sandbox_token_id'] = $sandboxTokenId;
            }

            // Only record IP address for non-sandbox, non-health/ready calls
            if (!$isSandboxRequest && !$isHealthOrReadinessEndpoint && in_array('ip_address', $columns)) {
                $logEntry['ip_address'] = $request->ip();
            }

            // Only record user agent for non-sandbox calls
            if (!$isSandboxRequest && in_array('user_agent', $columns)) {
                $logEntry['user_agent'] = $request->userAgent() ?? '';
            }

            // Set user_id and api_key_id for authenticated calls
            if (!$isHealthOrReadinessEndpoint && !$isSandboxRequest) {
                // The user might have been found at the beginning of this method
                if ($user) {
                    $logEntry['user_id'] = $user->id;

                    // Use the token ID we captured at the beginning
                    if ($tokenId && in_array('api_key_id', $columns)) {
                        $logEntry['api_key_id'] = $tokenId;
                        Log::debug("Setting api_key_id = {$tokenId} for log entry");
                    }
                } else {
                    // Attempt to find the user from API key
                    $apiKey = $request->header('X-API-Key');
                    if ($apiKey) {
                        Log::debug("Attempting to find user from X-API-Key header");

                        try {
                            // Look for the key in personal_access_tokens
                            // Note: This approach might not work if Sanctum uses a different hashing method
                            $accessToken = PersonalAccessToken::where('token', hash('sha256', $apiKey))->first();

                            if ($accessToken) {
                                $logEntry['user_id'] = $accessToken->tokenable_id;
                                $logEntry['api_key_id'] = $accessToken->id;

                                Log::debug("Found user ID {$accessToken->tokenable_id} and token ID {$accessToken->id} from API key");
                            }
                        } catch (\Exception $e) {
                            Log::warning("Error finding token from API key: " . $e->getMessage());
                        }
                    }
                }
            }

            // Filter entry to include only existing columns
            $filteredEntry = array_intersect_key($logEntry, array_flip($columns));

            // Only insert if we have data to insert
            if (!empty($filteredEntry)) {
                $logId = DB::table('api_endpoint_logs')->insertGetId($filteredEntry);

                // Debug log detailed info about what was stored
                Log::debug("API log entry created", [
                    'id' => $logId,
                    'endpoint' => $path,
                    'user_id' => $filteredEntry['user_id'] ?? null,
                    'api_key_id' => $filteredEntry['api_key_id'] ?? null,
                    'sandbox_token_id' => $filteredEntry['sandbox_token_id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error("Error logging API endpoint: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return $response;
    }
}
