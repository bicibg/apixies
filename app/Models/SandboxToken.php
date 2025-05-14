<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SandboxToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'calls',
        'quota',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if quota is exhausted
     *
     * @return bool
     */
    public function isQuotaExhausted(): bool
    {
        return $this->calls >= $this->quota;
    }

    /**
     * Increment the calls count
     *
     * @return void
     */
    public function incrementCalls(): void
    {
        $this->increment('calls');
    }

    /**
     * Find a sandbox token by its value
     *
     * @param string $token
     * @return SandboxToken|null
     */
    public static function findToken(string $token): ?SandboxToken
    {
        return static::where('token', $token)->first();
    }
}
