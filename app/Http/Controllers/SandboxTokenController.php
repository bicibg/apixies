<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SandboxTokenController extends Controller
{
    /**
     * Issue a new sandbox token via GET request
     *
     * @return JsonResponse
     */
    public function issue(): JsonResponse
    {
        // Generate a new token
        $token = Str::random(40);

        // Create a new sandbox token record
        SandboxToken::create([
            'token' => $token,
            'calls' => 0,             // Start with 0 calls
            'quota' => 50,            // Allow 50 calls per token
            'expires_at' => now()->addMinutes(30), // 30 minute expiry
        ]);

        // Return only the token to the client
        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * Create a new sandbox token via POST request
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return $this->issue();
    }

    /**
     * Refresh a sandbox token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->issue();
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

        $sandboxToken = SandboxToken::findToken($token);

        if (!$sandboxToken) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ]);
        }

        if ($sandboxToken->isExpired()) {
            return response()->json([
                'valid' => false,
                'message' => 'Sandbox token expired'
            ]);
        }

        if ($sandboxToken->isQuotaExhausted()) {
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
