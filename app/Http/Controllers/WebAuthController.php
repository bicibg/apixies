<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class WebAuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function createSession(Request $request)
    {
        $token = $request->input('token');

        // Extract token ID from token string
        $tokenParts = explode('|', $token);
        if (count($tokenParts) < 2) {
            return response()->json(['error' => 'Invalid token format'], 401);
        }

        $tokenId = $tokenParts[0];
        $personalAccessToken = PersonalAccessToken::find($tokenId);

        if (!$personalAccessToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Get the user associated with the token
        $user = $personalAccessToken->tokenable;

        // Login the user using Laravel's session-based auth
        Auth::login($user);

        return response()->json(['success' => true]);
    }

    public function logout(Request $request)
    {
        // Log out of web session
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear JavaScript token (done via JS)
        return redirect('/');
    }
}
