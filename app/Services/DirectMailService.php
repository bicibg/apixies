<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class DirectMailService
{
    /**
     * Send email verification
     * Automatically queues for web requests, sends directly for CLI
     */
    public static function sendEmailVerification($user)
    {
        if (self::shouldQueue()) {
            return self::queueEmail('verification', $user);
        }

        return self::sendVerificationEmailNow($user);
    }

    /**
     * Send password reset email
     * Automatically queues for web requests, sends directly for CLI
     */
    public static function sendPasswordReset($user, $token)
    {
        if (self::shouldQueue()) {
            return self::queueEmail('password_reset', $user, $token);
        }

        return self::sendPasswordResetNow($user, $token);
    }

    /**
     * Send account deactivated email
     * Automatically queues for web requests, sends directly for CLI
     */
    public static function sendAccountDeactivated($user)
    {
        if (self::shouldQueue()) {
            return self::queueEmail('account_deactivated', $user);
        }

        return self::sendAccountDeactivatedNow($user);
    }

    /**
     * Determine if we should queue emails based on context
     */
    private static function shouldQueue(): bool
    {
        // Queue if this is a web request, send directly if CLI
        return php_sapi_name() !== 'cli';
    }

    /**
     * Queue email sending
     */
    private static function queueEmail($type, $user, $token = null)
    {
        try {
            Queue::push(function ($job) use ($type, $user, $token) {
                switch ($type) {
                    case 'verification':
                        self::sendVerificationEmailNow($user);
                        break;
                    case 'password_reset':
                        self::sendPasswordResetNow($user, $token);
                        break;
                    case 'account_deactivated':
                        self::sendAccountDeactivatedNow($user);
                        break;
                }
                $job->delete();
            });

            Log::info("Email {$type} queued", ['user_id' => $user->id, 'email' => $user->email]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to queue {$type} email", [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send email verification immediately (synchronous)
     */
    public static function sendVerificationEmailNow($user)
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
     * Send password reset immediately (synchronous)
     */
    public static function sendPasswordResetNow($user, $token)
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
     * Send account deactivated immediately (synchronous)
     */
    public static function sendAccountDeactivatedNow($user)
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
