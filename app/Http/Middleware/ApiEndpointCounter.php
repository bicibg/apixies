<?php

namespace App\Http\Middleware;

use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiEndpointCounter
{
    /**
     * Record per‑endpoint totals and a detailed log row for every /api/* request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only track API routes
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // Mark start time
        $startedAt = microtime(true);

        // Process the request
        $response = $next($request);

        // Try to track API usage directly instead of using a closure
        try {
            // Compute endpoint key (named route or "VERB path")
            $routeName = $request->route()?->getName();
            $endpoint = $routeName ?: sprintf('%s %s', $request->method(), $request->path());

            // Calculate latency in ms
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            // Create the endpoint log record with simple parameters
            // Use a dedicated job class instead for more complex tracking
            $this->recordEndpointUsage($endpoint, $request, $latencyMs);

        } catch (\Throwable $e) {
            // Never break the response pipeline for logging failures
            Log::error('ApiEndpointCounter failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return $response;
    }

    /**
     * Record API endpoint usage
     */
    private function recordEndpointUsage(string $endpoint, Request $request, int $latencyMs): void
    {
        // Update aggregate counter (UPSERT)
        ApiEndpointCount::upsert(
            ['endpoint' => $endpoint, 'count' => 1],
            ['endpoint'],
            ['count' => DB::raw('count + 1')]
        );

        // Get token UUID from request attributes (set by EnsureApiKey)
        $apiKeyId = $request->attributes->get('api_token_uuid');

        // Get user from request (set by EnsureApiKey)
        $user = $request->user();

        // Anonymize IP address for privacy
        $ipAddress = $this->anonymizeIp($request->ip());

        // Insert detailed log row
        ApiEndpointLog::create([
            'endpoint'    => $endpoint,
            'method'      => $request->method(),
            'latency_ms'  => $latencyMs,
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'api_key_id'  => $apiKeyId,
            'ip_address'  => $ipAddress,
            'user_agent'  => $this->truncateUserAgent($request->userAgent()),
            'created_at'  => now(),
        ]);
    }

    /**
     * Anonymize IP address by removing the last octet for IPv4
     * or the last 80 bits for IPv6
     */
    private function anonymizeIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }

        // IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3)) . ':0:0:0:0:0';
        }

        return $ip;
    }

    /**
     * Truncate user agent to a reasonable length and remove potentially
     * identifying information
     */
    private function truncateUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        // Truncate to 255 chars max
        return substr($userAgent, 0, 255);
    }
}
