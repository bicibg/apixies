<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class DirectMailService
{
    /**
     * Send email verification
     */
    public static function sendEmailVerification($user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        try {
            Mail::send('vendor.mail.notifications.email-verification', [
                'userName' => $user->name,
                'verificationUrl' => $verificationUrl,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email Address');
            });

            Log::info('Email verification sent', ['user_id' => $user->id, 'email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset($user, $token)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        try {
            Mail::send('vendor.mail.notifications.password-reset', [
                'userName' => $user->name,
                'resetUrl' => $resetUrl,
                'expireMinutes' => config('auth.passwords.users.expire', 60),
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password Notification');
            });

            Log::info('Password reset email sent', ['user_id' => $user->id, 'email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send account deactivated email
     */
    public static function sendAccountDeactivated($user)
    {
        $restoreUrl = URL::temporarySignedRoute(
            'profile.restore',
            now()->addDays(30),
            ['id' => $user->id]
        );

        try {
            Mail::send('vendor.mail.notifications.account-deactivated', [
                'userName' => $user->name,
                'restoreUrl' => $restoreUrl,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Apixies Account Has Been Deactivated');
            });

            Log::info('Account deactivated email sent', ['user_id' => $user->id, 'email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Account deactivated email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
