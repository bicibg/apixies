<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/v1/register
    public function register(Request $req)
    {
        $data = $req->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('default')->plainTextToken;

        return ApiResponse::success(
            ['token' => $token],
            'Registration successful',
            'REGISTERED'
        );
    }

    // POST /api/v1/login
    public function login(Request $req)
    {
        $data = $req->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('default')->plainTextToken;

        return ApiResponse::success(
            ['token' => $token],
            'Login successful',
            'LOGGED_IN'
        );
    }

    // POST /api/v1/logout
    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return ApiResponse::success([], 'Logged out', 'LOGGED_OUT');
    }
}
