<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SandboxTokenController extends Controller
{
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
}
