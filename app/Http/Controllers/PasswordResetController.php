<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    // POST /api/v1/password/forgot
    public function sendLink(Request $req)
    {
        $req->validate(['email'=>'required|email|exists:users,email']);

        $status = Password::sendResetLink($req->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success([], 'Reset link sent');
        }

        throw ValidationException::withMessages([
            'email' => ['Unable to send reset link.'],
        ]);
    }

    // POST /api/v1/password/reset
    public function reset(Request $req)
    {
        $req->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $req->only('email','token','password','password_confirmation'),
            fn($user, $password) => $user->forceFill([
                'password'       => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save()
        );

        if ($status === Password::PASSWORD_RESET) {
            event(new PasswordReset($req->user()));
            return ApiResponse::success([], 'Password has been reset');
        }

        throw ValidationException::withMessages([
            'token' => ['Invalid or expired token.'],
        ]);
    }
}
