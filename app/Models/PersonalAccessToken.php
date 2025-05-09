<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PersonalAccessToken extends SanctumToken
{
    protected $guarded = ['id'];

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
        // if there's no expiry set, treat it as never expiring
        if (! $this->expires_at) {
            return false;
        }

        return Carbon::now()->greaterThan($this->expires_at);
        // or: return $this->expires_at->isPast();
    }
}
