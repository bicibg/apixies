<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    // GET /api/v1/api-keys
    public function index(Request $req)
    {
        $keys = $req->user()
            ->tokens()
            ->get(['id','name','created_at','last_used_at']);

        return ApiResponse::success($keys);
    }

    // POST /api/v1/api-keys
    public function store(Request $req)
    {
        $data = $req->validate([
            'name'      => 'required|string|max:255',
            'abilities' => 'array',
        ]);

        $tokenResult = $req->user()
            ->createToken($data['name'], $data['abilities'] ?? ['*']);

        return ApiResponse::success([
            'id'    => $tokenResult->accessToken->id,
            'name'  => $tokenResult->accessToken->name,
            'token' => $tokenResult->plainTextToken,
        ], 'API key created', 'API_KEY_CREATED');
    }

    // DELETE /api/v1/api-keys/{id}
    public function destroy(Request $req, $id)
    {
        $req->user()->tokens()->where('id',$id)->delete();

        return ApiResponse::success([], 'API key revoked', 'API_KEY_REVOKED');
    }
}
