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
     * Maximum tokens per day per IP
     */
    const MAX_TOKENS_PER_DAY = 1;

    /**
     * Token call quota
     */
    const DEFAULT_QUOTA = 25;

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

            // Check for existing active token for this IP to avoid token farming
            $existingValidToken = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('calls', '<', DB::raw('quota'))
                ->where('expires_at', '>', now())
                ->first();

            if ($existingValidToken) {
                // Return existing token
                return response()->json([
                    'token' => $existingValidToken->token,
                    'expires_at' => $existingValidToken->expires_at,
                    'quota' => $existingValidToken->quota,
                    'remaining_calls' => $existingValidToken->quota - $existingValidToken->calls,
                    'message' => 'Using existing valid token'
                ]);
            }

            // Check for daily token limit (1 token per IP per day)
            $tokenCount = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('created_at', '>', now()->startOfDay())
                ->count();

            if ($tokenCount >= self::MAX_TOKENS_PER_DAY) {
                // Find the most recent token for this IP
                $latestToken = DB::table('sandbox_tokens')
                    ->where('ip_address', $ip)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestToken) {
                    // If token exists but is expired, reactivate it
                    if (now()->greaterThan($latestToken->expires_at)) {
                        DB::table('sandbox_tokens')
                            ->where('id', $latestToken->id)
                            ->update([
                                'expires_at' => now()->addMinutes(30),
                                'updated_at' => now()
                            ]);

                        return response()->json([
                            'token' => $latestToken->token,
                            'expires_at' => now()->addMinutes(30),
                            'quota' => $latestToken->quota,
                            'remaining_calls' => $latestToken->quota - $latestToken->calls,
                            'message' => 'Token reactivated with same quota'
                        ]);
                    }

                    // If token exists but quota exhausted, return error/warning
                    if ($latestToken->calls >= $latestToken->quota) {
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Daily token limit reached and quota exhausted. Please try again tomorrow.',
                            'retry_after' => now()->addDay()->startOfDay()->diffForHumans(),
                            'token_limit_reached' => true
                        ], 429);
                    }
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Daily token limit reached. Please try again tomorrow.',
                    'retry_after' => now()->addDay()->startOfDay()->diffForHumans(),
                    'token_limit_reached' => true
                ], 429);
            }

            // Generate a new token
            $token = Str::random(40);

            // Insert new token
            DB::table('sandbox_tokens')->insert([
                'token' => $token,
                'calls' => 0,
                'quota' => self::DEFAULT_QUOTA,
                'ip_address' => $ip,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addMinutes(30)
            ]);

            return response()->json([
                'token' => $token,
                'expires_at' => now()->addMinutes(30),
                'quota' => self::DEFAULT_QUOTA,
                'remaining_calls' => self::DEFAULT_QUOTA
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

            // Find existing tokens for this IP
            $existingToken = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$existingToken) {
                // No existing token, create a new one
                return $this->create();
            }

            // Token exists but quota is exhausted
            if ($existingToken->calls >= $existingToken->quota) {
                // Check daily token limit
                $tokenCount = DB::table('sandbox_tokens')
                    ->where('ip_address', $ip)
                    ->where('created_at', '>', now()->startOfDay())
                    ->count();

                if ($tokenCount >= self::MAX_TOKENS_PER_DAY) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Daily token limit reached and quota exhausted. Please try again tomorrow.',
                        'retry_after' => now()->addDay()->startOfDay()->diffForHumans(),
                        'token_limit_reached' => true
                    ], 429);
                }

                // If under daily limit, create a new token
                return $this->create();
            }

            // Token exists and has remaining quota, just refresh expiration
            DB::table('sandbox_tokens')
                ->where('id', $existingToken->id)
                ->update([
                    'expires_at' => now()->addMinutes(30),
                    'updated_at' => now()
                ]);

            return response()->json([
                'token' => $existingToken->token,
                'message' => 'Token expiration refreshed',
                'expires_at' => now()->addMinutes(30),
                'quota' => $existingToken->quota,
                'remaining_calls' => $existingToken->quota - $existingToken->calls
            ]);
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
                    'remaining_calls' => max(0, $sandboxToken->quota - $sandboxToken->calls)
                ]);
            }

            if ($isQuotaExceeded) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Sandbox quota exhausted',
                    'quota_exceeded' => true,
                    'remaining_calls' => 0
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
