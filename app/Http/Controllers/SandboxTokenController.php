<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SandboxTokenController extends Controller
{
    /**
     * Issue a new sandbox token
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        // Generate a new token
        $token = Str::random(40);

        // Create a new sandbox token record
        SandboxToken::create([
            'token' => $token,
            'calls' => 0,
            'quota' => 50, // 50 requests per token
            'expires_at' => now()->addMinutes(30), // 30 minute expiry
        ]);

        // Return only the token to the client
        return response()->json([
            'token' => $token
        ]);
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
     * Validate a sandbox token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (empty($token)) {
            return response()->json([
                'valid' => false,
                'message' => 'No token provided'
            ]);
        }

        $sandboxToken = SandboxToken::where('token', $token)->first();

        if (!$sandboxToken) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ]);
        }

        if ($sandboxToken->expires_at && now()->greaterThan($sandboxToken->expires_at)) {
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

        return response()->json([
            'valid' => true,
            'message' => 'Token is valid',
            'remaining_calls' => $sandboxToken->quota - $sandboxToken->calls,
            'expires_at' => $sandboxToken->expires_at->toIso8601String()
        ]);
    }
}
