<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

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
            // Generate a new token
            $token = Str::random(40);

            // Check if expires_at column exists
            $hasExpiresAt = \Schema::hasColumn('sandbox_tokens', 'expires_at');

            // Create token with or without expiry based on schema
            $tokenData = [
                'token' => $token,
                'calls' => 0,
                'quota' => 50, // 50 requests per token
            ];

            // Add expires_at only if the column exists
            if ($hasExpiresAt) {
                $tokenData['expires_at'] = now()->addMinutes(30); // 30 minute expiry
            }

            // Create the token
            SandboxToken::create($tokenData);

            // Return only the token to the client
            return response()->json([
                'token' => $token
            ]);
        } catch (QueryException $e) {
            // Handle database errors
            Log::error('Failed to create sandbox token: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create sandbox token',
                'message' => 'Database error occurred'
            ], 500);
        } catch (\Exception $e) {
            // Handle other errors
            Log::error('Unexpected error creating sandbox token: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create sandbox token',
                'message' => 'Unexpected error occurred'
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
        // This is just a proxy to create for backward compatibility
        return $this->create();
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
            $sandboxToken = SandboxToken::where('token', $token)->first();
            if ($sandboxToken) {
                $sandboxToken->calls = $sandboxToken->calls + 1;
                $result = $sandboxToken->save();
                Log::info('Token usage incremented for token: ' . substr($token, 0, 8) . '..., new count: ' . $sandboxToken->calls);
                return $result;
            }
            return false;
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
            $sandboxToken = SandboxToken::where('token', $token)->first();

            if (!$sandboxToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid token'
                ]);
            }

            // Check if expires_at column exists and handle accordingly
            $hasExpiresAt = \Schema::hasColumn('sandbox_tokens', 'expires_at');
            $expiryCheck = false;
            $expiryData = null;

            if ($hasExpiresAt && $sandboxToken->expires_at && now()->greaterThan($sandboxToken->expires_at)) {
                $expiryCheck = true;
                $expiryData = $sandboxToken->expires_at;
            }

            if ($expiryCheck) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Sandbox token expired'
                ]);
            }

            if ($sandboxToken->calls >= $sandboxToken->quota) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Sandbox quota exhausted'
                ]);
            }

            $response = [
                'valid' => true,
                'message' => 'Token is valid',
                'remaining_calls' => $sandboxToken->quota - $sandboxToken->calls,
            ];

            // Add expires_at only if it exists
            if ($hasExpiresAt && $sandboxToken->expires_at) {
                $response['expires_at'] = $sandboxToken->expires_at->toIso8601String();
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
