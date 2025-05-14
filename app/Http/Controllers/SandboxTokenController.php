<?php

namespace App\Http\Controllers;

use App\Models\SandboxToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SandboxTokenController extends Controller
{
    public function issue(): JsonResponse
    {
        $token = Str::random(40);
        $ttl   = 1800;  // 30 minutes
        $quota = 100;

        // Create token in database
        SandboxToken::create([
            'token' => $token,
            'calls' => 0,
            'quota' => $quota,
            'expires_in' => $ttl,
        ]);

        return response()->json([
            'token'      => $token,
            'expires_in' => $ttl,
            'quota'      => $quota,
        ]);
    }
}
