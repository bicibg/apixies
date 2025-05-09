<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PersonalAccessToken extends SanctumToken
{
    protected $guarded = ['id'];

    /**
     * Bootstrap the model and its event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (PersonalAccessToken $token) {
            // Always generate a UUID for new tokens
            $token->uuid = (string) Str::uuid();

            // Initialize last_used_at to now
            $token->last_used_at = Carbon::now();
        });
    }
}
