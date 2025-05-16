<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiEndpointLog extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'is_sandbox' => 'boolean',
    ];

    /**
     * Get the user associated with this log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
