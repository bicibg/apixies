<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuggestionVote extends Model
{
    protected $fillable = ['suggestion_id', 'user_id', 'anon_hash'];

    public function suggestion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Suggestion::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
