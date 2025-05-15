<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SandboxTokenController extends Controller
{
    /**
     * Issue a new sandbox token
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        try {
            // Get the client IP address with fallback
            $ip = request()->ip() ?: '127.0.0.1';
            Log::info('Creating sandbox token for IP: ' . $ip);

            // Check for rate limiting - no more than 3 tokens per hour per IP
            $tokenCount = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('created_at', '>', now()->subHour())
                ->count();

            if ($tokenCount >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'rate_limited' => true,
                    'retry_after' => now()->addHour()->diffInMinutes(now()) . ' minutes'
                ], 429);
            }

            // Generate a new token
            $token = Str::random(40);

            // Directly insert to avoid model quirks
            DB::table('sandbox_tokens')->insert([
                'token' => $token,
                'calls' => 0,
                'quota' => 50,
                'ip_address' => $ip,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addMinutes(30)
            ]);

            // Verify it was inserted correctly
            $inserted = DB::table('sandbox_tokens')
                ->where('token', $token)
                ->first();

            Log::info('Inserted token record', [
                'token_id' => $inserted->id ?? 'unknown',
                'ip_stored' => $inserted->ip_address ?? 'not set',
                'token_start' => substr($token, 0, 8)
            ]);

            return response()->json([
                'token' => $token,
                'expires_at' => now()->addMinutes(30),
                'quota' => 50
            ]);
        } catch (QueryException $e) {
            Log::error('Database error creating sandbox token: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'code' => 'DB_ERROR',
                'message' => 'Failed to create sandbox token'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error creating sandbox token: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Failed to create sandbox token'
            ], 500);
        }
    }

    /**
     * Refresh a sandbox token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            // Get the client IP address
            $ip = request()->ip() ?: '127.0.0.1';

            // Check for rate limiting - max 5 refreshes per hour
            $refreshCount = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('updated_at', '>', now()->subHour())
                ->where('created_at', '<>', DB::raw('updated_at'))
                ->count();

            if ($refreshCount >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token refresh rate limit exceeded. Please try again later.',
                    'rate_limited' => true,
                    'retry_after' => now()->addHour()->diffInMinutes(now()) . ' minutes'
                ], 429);
            }

            // Find existing tokens for this IP
            $existingToken = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existingToken) {
                // Update the existing token's expiration and reset calls
                DB::table('sandbox_tokens')
                    ->where('id', $existingToken->id)
                    ->update([
                        'expires_at' => now()->addMinutes(30),
                        'calls' => 0,
                        'updated_at' => now()
                    ]);

                return response()->json([
                    'token' => $existingToken->token,
                    'message' => 'Token refreshed successfully',
                    'expires_at' => now()->addMinutes(30),
                    'quota' => $existingToken->quota
                ]);
            }

            // No existing token, create a new one
            return $this->create();
        } catch (\Exception $e) {
            Log::error('Error refreshing token: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to refresh token',
                'message' => 'Unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Increment token usage count
     *
     * @param string $token
     * @return bool
     */
    public function incrementUsage(string $token): bool
    {
        try {
            $result = DB::table('sandbox_tokens')
                ->where('token', $token)
                ->increment('calls', 1, [
                    'updated_at' => now()
                ]);

            Log::info('Token usage incremented for token: ' . substr($token, 0, 8) . '...', [
                'result' => $result ? 'success' : 'failed'
            ]);

            return $result > 0;
        } catch (\Exception $e) {
            Log::error('Error incrementing token usage: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate a sandbox token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (empty($token)) {
            return response()->json([
                'valid' => false,
                'message' => 'No token provided'
            ]);
        }

        try {
            $sandboxToken = DB::table('sandbox_tokens')
                ->where('token', $token)
                ->first();

            if (!$sandboxToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid token'
                ]);
            }

            // Check if expires_at exists and token is expired
            $isExpired = property_exists($sandboxToken, 'expires_at') &&
                $sandboxToken->expires_at &&
                now()->greaterThan($sandboxToken->expires_at);

            // Check if quota is exceeded
            $isQuotaExceeded = $sandboxToken->calls >= $sandboxToken->quota;

            if ($isExpired) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Sandbox token expired',
                    'expired' => true,
                    'remaining_calls' => $sandboxToken->quota - $sandboxToken->calls
                ]);
            }

            if ($isQuotaExceeded) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Sandbox quota exhausted',
                    'quota_exceeded' => true
                ]);
            }

            $response = [
                'valid' => true,
                'message' => 'Token is valid',
                'remaining_calls' => $sandboxToken->quota - $sandboxToken->calls,
            ];

            // Add expires_at if it exists
            if (property_exists($sandboxToken, 'expires_at') && $sandboxToken->expires_at) {
                $response['expires_at'] = $sandboxToken->expires_at;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error validating token: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'message' => 'Error validating token'
            ], 500);
        }
    }
}
