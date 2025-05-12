<?php

namespace App\Http\Middleware;

use App\Models\ApiEndpointCount;
use App\Models\ApiEndpointLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class ApiEndpointCounter
{
    /**
     * Record per‑endpoint totals and a detailed log row for every /api/* request.
     *
     * ──────────────────────────────────────────────────────────────────────────
     * • Endpoint totals → api_endpoint_counts  (UPSERT  count = count + 1)
     * • Detailed log    → api_endpoint_logs    (1 row per request)
     *     ‑ endpoint (string   “GET /api/v1/inspect-email”)
     *     ‑ method   (string   GET / POST …)
     *     ‑ latency_ms (int    server‑side ms)
     *     ‑ user_id / api_key_id  (nullable)
     * ──────────────────────────────────────────────────────────────────────────
     */
    public function handle(Request $request, Closure $next)
    {
        /* ── 1. mark start time ─────────────────────────────────────────── */
        $startedAt = microtime(true);

        /* ── 2. let the request proceed ─────────────────────────────────── */
        $response = $next($request);

        /* ── 3. only track API routes (/api/*) ──────────────────────────── */
        if (! $request->is('api/*')) {
            return $response;
        }

        try {
            /* a. compute endpoint key (named route or “VERB path”) */
            $routeName = $request->route()?->getName();
            $endpoint  = $routeName ?: sprintf('%s %s', $request->method(), $request->path());

            /* b. update aggregate counter (UPSERT) */
            ApiEndpointCount::upsert(
                ['endpoint' => $endpoint, 'count' => 1],
                ['endpoint'],
                ['count' => DB::raw('count + 1')]
            );

            /* c. resolve token → user (if any) */
            $rawToken = $request->bearerToken() ?: $request->header('X-API-KEY');
            $pat      = $rawToken ? PersonalAccessToken::findToken($rawToken) : null;

            $user     = $pat?->tokenable;
            $apiKeyId = $pat?->uuid;

            /* d. latency in ms (rounded) */
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            /* e. insert detailed log row */
            ApiEndpointLog::create([
                'endpoint'    => $endpoint,
                'method'      => $request->method(),
                'latency_ms'  => $latencyMs,
                'user_id'     => $user?->id,
                'user_name'   => $user?->name,
                'api_key_id'  => $apiKeyId,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'created_at'  => now(),   // timestamps = false on model
            ]);
        } catch (\Throwable $e) {
            // Never break the response pipeline for logging failures
            Log::error('ApiEndpointCounter failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return $response;
    }
}
