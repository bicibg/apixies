<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PersonalAccessToken extends SanctumToken
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     * (Not strictly necessary in Laravel 8+, but good clarity.)
     */
    protected $dates = [
        'last_used_at',
        'expires_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (PersonalAccessToken $token) {
            $token->uuid         = (string) Str::uuid();
            $token->last_used_at = Carbon::now();
        });
    }

    /**
     * Determine if this token is expired.
     */
    public function expired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return Carbon::now()->greaterThan($this->expires_at);
    }
}
