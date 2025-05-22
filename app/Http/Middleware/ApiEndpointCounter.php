<?php

namespace App\Http\Middleware;

use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use App\Models\SandboxToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ApiEndpointCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
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
                $token = SandboxToken::where('token', $sandboxToken)->first();
                if ($token) {
                    $sandboxTokenId = $token->id;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get sandbox token ID: " . $e->getMessage());
            }
        }

        // Increment API endpoint count
        try {
            // Check if table exists
            if (!Schema::hasTable('api_endpoint_counts')) {
                Log::error("api_endpoint_counts table doesn't exist");
                return $response;
            }

            $this->updateApiEndpointCount($path, $isSandboxRequest);
        } catch (\Exception $e) {
            // Log the error but don't disrupt the response
            Log::error("Error incrementing API endpoint count: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        // Also log to the ApiEndpointLog model with more details
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
            if (!$isHealthOrReadinessEndpoint && in_array('ip_address', $columns)) {
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
                    }
                } else {
                    // Attempt to find the user from API key
                    $apiKey = $request->header('X-API-Key');
                    if ($apiKey) {
                        try {
                            // Look for the key in personal_access_tokens
                            // Note: This approach might not work if Sanctum uses a different hashing method
                            $accessToken = PersonalAccessToken::where('token', hash('sha256', $apiKey))->first();

                            if ($accessToken) {
                                $logEntry['user_id'] = $accessToken->tokenable_id;
                                $logEntry['api_key_id'] = $accessToken->id;
                            }
                        } catch (\Exception $e) {
                            Log::warning("Error finding token from API key: " . $e->getMessage());
                        }
                    }
                }
            }

            // Create log entry
            $this->createApiEndpointLog($logEntry, $columns);
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

    /**
     * Update or create API endpoint count.
     *
     * @param  string  $path
     * @param  bool  $isSandboxRequest
     * @return void
     * @throws \Exception
     */
    private function updateApiEndpointCount(string $path, bool $isSandboxRequest): void
    {
        try {
            // Try to find and update the existing record
            $endpointCount = ApiEndpointCount::where('endpoint', $path)
                ->where('is_sandbox', $isSandboxRequest)
                ->first();

            if ($endpointCount) {
                // Increment the count for existing record
                $endpointCount->increment('count');
                $endpointCount->updated_at = now();
                $endpointCount->save();
            } else {
                // Create a new record
                ApiEndpointCount::create([
                    'endpoint' => $path,
                    'is_sandbox' => $isSandboxRequest,
                    'count' => 1,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error in updateApiEndpointCount: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create API endpoint log entry.
     *
     * @param  array  $logEntry
     * @param  array  $columns
     * @return void
     * @throws \Exception
     */
    private function createApiEndpointLog(array $logEntry, array $columns): void
    {
        try {
            // Filter entry to include only existing columns
            $filteredEntry = array_intersect_key($logEntry, array_flip($columns));

            // Only insert if we have data to insert
            if (!empty($filteredEntry)) {
                ApiEndpointLog::create($filteredEntry);
            }
        } catch (\Exception $e) {
            Log::error("Error in createApiEndpointLog: " . $e->getMessage());
            throw $e;
        }
    }
}
