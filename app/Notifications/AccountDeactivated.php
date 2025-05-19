<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AccountDeactivated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Generate a signed URL for account restoration
        $url = URL::temporarySignedRoute(
            'profile.restore',
            now()->addDays(30),
            ['id' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject('Your Apixies Account Has Been Deactivated')
            ->line('Your Apixies account has been deactivated as requested.')
            ->line('If you believe this was done in error or would like to restore your account, you can do so within the next 30 days by clicking the button below.')
            ->action('Restore Account', $url)
            ->line('After 30 days, your account and all associated data may be permanently deleted.')
            ->line('If you did not request this account deactivation, please contact our support team immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
