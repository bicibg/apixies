<?php

namespace App\Models;

use App\Services\DirectMailService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * Mass-assignable attributes.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Hidden attributes in arrays/JSON.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Attribute casts.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_admin' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Send the password reset notification.
     * Override to use direct mail instead of notification system
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // Use direct mail service instead of notification
        DirectMailService::sendPasswordReset($this, $token);
    }

    /**
     * Send the email verification notification.
     * Override to use direct mail instead of notification system
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // Use direct mail service instead of notification
        DirectMailService::sendEmailVerification($this);
    }

    /**
     * Send account deactivated notification
     * Custom method using direct mail
     *
     * @return void
     */
    public function sendAccountDeactivatedNotification(): void
    {
        DirectMailService::sendAccountDeactivated($this);
    }

    /**
     * Check if the user can access the Filament admin panel.
     *
     * @param  Panel  $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
