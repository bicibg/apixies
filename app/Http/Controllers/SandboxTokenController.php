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
     * Create a new token or handle an expired token
     *
     * @param string|null $oldToken Old token to replace (optional)
     * @return JsonResponse
     */
    public function create(?string $oldToken = null): JsonResponse
    {
        try {
            $ip = request()->ip() ?: '127.0.0.1';
            Log::info('Creating sandbox token for IP: ' . $ip);

            // If old token is provided, delete it first
            if ($oldToken) {
                DB::table('sandbox_tokens')
                    ->where('token', $oldToken)
                    ->delete();

                Log::info('Deleted old token: ' . $oldToken);
            }

            // Check for existing active token for this IP to avoid token farming
            $existingValidToken = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('calls', '<', DB::raw('quota'))
                ->where('expires_at', '>', now())
                ->first();

            if ($existingValidToken) {
                return response()->json([
                    'token' => $existingValidToken->token,
                    'expires_at' => $existingValidToken->expires_at,
                    'quota' => $existingValidToken->quota,
                    'remaining_calls' => $existingValidToken->quota - $existingValidToken->calls,
                    'message' => 'Using existing valid token'
                ]);
            }

            $tokenCount = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->where('created_at', '>', now()->startOfDay())
                ->count();

            if ($tokenCount >= self::MAX_TOKENS_PER_DAY) {
                $latestToken = DB::table('sandbox_tokens')
                    ->where('ip_address', $ip)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestToken) {
                    if (now()->greaterThan($latestToken->expires_at)) {
                        // If token is expired but same day, reactivate it
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

            $token = Str::random(40);

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
            $ip = request()->ip() ?: '127.0.0.1';

            // Get latest token for this IP
            $existingToken = DB::table('sandbox_tokens')
                ->where('ip_address', $ip)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$existingToken) {
                return $this->create();
            }

            // Check if it's a new day (since token creation)
            $isNewDay = now()->startOfDay()->gt(
                now()->parse($existingToken->created_at)->startOfDay()
            );

            // Check if token is expired
            $isExpired = now()->gt($existingToken->expires_at);

            // If it's a new day or token is expired, create a new token
            if ($isNewDay || $isExpired) {
                Log::info('Creating new token because: ' . ($isNewDay ? 'new day' : 'token expired'));

                // Delete the old token since we're creating a new one
                DB::table('sandbox_tokens')
                    ->where('id', $existingToken->id)
                    ->delete();

                return $this->create();
            }

            // Handle quota exceeded tokens
            if ($existingToken->calls >= $existingToken->quota) {
                $tokenCount = DB::table('sandbox_tokens')
                    ->where('ip_address', $ip)
                    ->where('created_at', '>', now()->startOfDay())
                    ->count();

                if ($tokenCount >= self::MAX_TOKENS_PER_DAY) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Daily token limit reached. Please try again tomorrow.',
                        'retry_after' => now()->addDay()->startOfDay()->diffForHumans(),
                        'token_limit_reached' => true
                    ], 429);
                }

                // Create a new token if quota is exceeded
                return $this->create();
            }

            // Refresh expiration time of valid token with quota remaining
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
                ->where('token', 'like', '%' . $token . '%')  // Use LIKE to handle potential partial tokens
                ->first();

            if (!$sandboxToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid token',
                    'needs_new_token' => true
                ]);
            }

            // Check if it's a new day since token creation - if so, should renew token
            $isNewDay = now()->startOfDay()->gt(
                now()->parse($sandboxToken->created_at)->startOfDay()
            );

            // Check if expires_at exists and token is expired
            $isExpired = property_exists($sandboxToken, 'expires_at') &&
                $sandboxToken->expires_at &&
                now()->greaterThan($sandboxToken->expires_at);

            $isQuotaExceeded = $sandboxToken->calls >= $sandboxToken->quota;

            // Always return token info even if expired or exhausted
            $response = [
                'valid' => !$isExpired && !$isQuotaExceeded,
                'token' => $sandboxToken->token,
                'remaining_calls' => max(0, $sandboxToken->quota - $sandboxToken->calls),
                'new_day' => $isNewDay
            ];

            if (property_exists($sandboxToken, 'expires_at') && $sandboxToken->expires_at) {
                $response['expires_at'] = $sandboxToken->expires_at;
            }

            if ($isExpired) {
                $response['message'] = 'Sandbox token expired';
                $response['expired'] = true;
                $response['needs_new_token'] = true;
            } elseif ($isQuotaExceeded) {
                $response['message'] = 'Sandbox quota exhausted';
                $response['quota_exceeded'] = true;
                $response['needs_new_token'] = true;
            } else {
                $response['message'] = 'Token is valid';

                // If it's a new day but token is still valid, suggest refreshing
                if ($isNewDay) {
                    $response['needs_refresh'] = true;
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error validating token: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            return response()->json([
                'valid' => false,
                'message' => 'Error validating token: ' . $e->getMessage(),
                'token' => $token,
                'needs_new_token' => true
            ], 500);
        }
    }
}
