<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * A simple test endpoint that requires authentication
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        return ApiResponse::success([
            'message' => 'API key is valid',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toISOString(),
            ],
            'token_info' => [
                'name' => $request->bearerToken() ? 'Bearer Token' : 'API Key',
                'abilities' => $user->currentAccessToken()->abilities ?? ['*'],
            ]
        ], 'API key validation successful');
    }
}
