<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SandboxToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'calls',
        'quota',
        'expires_in',
    ];

    /**
     * Check if the token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $expiresAt = $this->created_at->addSeconds($this->expires_in);
        return now()->greaterThan($expiresAt);
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
