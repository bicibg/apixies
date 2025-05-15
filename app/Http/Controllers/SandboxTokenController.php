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
                'token' => $token
            ]);
        } catch (QueryException $e) {
            Log::error('Database error creating sandbox token: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create sandbox token',
                'message' => 'Database error occurred: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error creating sandbox token: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create sandbox token',
                'message' => 'Unexpected error occurred: ' . $e->getMessage()
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

            // Log token details for debugging
            Log::info('Validating token', [
                'token_id' => $sandboxToken->id ?? 'unknown',
                'ip_stored' => $sandboxToken->ip_address ?? 'not set',
                'token_start' => substr($token, 0, 8),
                'calls' => $sandboxToken->calls ?? 0,
                'quota' => $sandboxToken->quota ?? 0
            ]);

            // Check if expires_at exists and token is expired
            if (property_exists($sandboxToken, 'expires_at') &&
                $sandboxToken->expires_at &&
                now()->greaterThan($sandboxToken->expires_at)) {
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

            // Add expires_at if it exists
            if (property_exists($sandboxToken, 'expires_at') && $sandboxToken->expires_at) {
                $response['expires_at'] = $sandboxToken->expires_at;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error validating token: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'message' => 'Error validating token: ' . $e->getMessage()
            ], 500);
        }
    }
}
