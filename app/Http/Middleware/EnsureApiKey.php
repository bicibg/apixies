<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\SandboxToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Grab the token from the Authorization header or X-API-KEY
        $raw = $request->bearerToken() ?: $request->header('X-API-KEY');

        if (!$raw) {
            Log::warning('API key missing', ['path' => $request->path()]);
            return ApiResponse::error('No API key provided', Response::HTTP_UNAUTHORIZED);
        }

        // For debugging: Store token validation debug info
        $debug = ['token_prefix' => substr($raw, 0, 8) . '...', 'path' => $request->path()];

        // 2) Check for a sandbox token
        $sandboxToken = SandboxToken::findToken($raw);

        if ($sandboxToken) {
            $debug['source'] = 'sandbox';
            Log::info('Sandbox token found', $debug);

            // Check expiration
            if ($sandboxToken->isExpired()) {
                // Clean up expired token
                $sandboxToken->delete();
                Log::warning('Sandbox token expired', $debug);
                return ApiResponse::error('Sandbox token expired', Response::HTTP_UNAUTHORIZED);
            }

            // Check quota
            if ($sandboxToken->isQuotaExhausted()) {
                Log::warning('Sandbox quota exhausted', $debug);
                return ApiResponse::error('Sandbox quota exhausted', Response::HTTP_TOO_MANY_REQUESTS);
            }

            // Update call count
            $sandboxToken->incrementCalls();

            // Set a demo user for sandbox tokens
            // This is critical for routes that expect $request->user() to be available
            $demoUser = $this->getDemoUser();
            $request->setUserResolver(fn() => $demoUser);

            Log::info('Sandbox token authorized', array_merge($debug, ['calls' => $sandboxToken->calls]));
            return $next($request);
        }

        // 3) Standard API token lookup
        $token = PersonalAccessToken::findToken($raw);

        if (!$token) {
            $debug['token_hash'] = hash('sha256', $raw);
            Log::warning('API key invalid', $debug);
            return ApiResponse::error('Invalid API key', Response::HTTP_UNAUTHORIZED);
        }

        // 4) Expiration check
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            $debug['uuid'] = $token->uuid;
            Log::warning('API key expired', $debug);
            return ApiResponse::error('API key expired', Response::HTTP_UNAUTHORIZED);
        }

        // 5) Scope check
        if (!$token->can('read')) {
            $debug['uuid'] = $token->uuid;
            Log::warning('API key missing read scope', $debug);
            return ApiResponse::error('Insufficient scope', Response::HTTP_FORBIDDEN);
        }

        // 6) All good — update last_used_at
        $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();
        $request->setUserResolver(fn() => $token->tokenable);

        $debug['uuid'] = $token->uuid;
        Log::info('API key authorized', $debug);

        return $next($request);
    }

    /**
     * Get a demo user for sandbox tokens
     *
     * @return User
     */
    protected function getDemoUser(): User
    {
        // Try to get the first admin user or create a dummy one
        $demoUser = User::where('email', 'sandbox@example.com')->first();

        if (!$demoUser) {
            // Check for any existing user
            $demoUser = User::first();

            // If no users exist, create a dummy one
            if (!$demoUser) {
                $demoUser = new User();
                $demoUser->id = 1; // Force ID to 1
                $demoUser->name = 'Sandbox User';
                $demoUser->email = 'sandbox@example.com';

                // We're not saving this user to the database
                // It's just a dummy object to satisfy routes that need a user
            }
        }

        return $demoUser;
    }
}
