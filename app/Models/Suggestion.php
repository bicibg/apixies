<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = [
        'title',
        'details',
        'author_id',
        'author_email',
        'status',
    ];

    protected $casts = [
        'votes' => 'integer',
    ];

    /* relationships */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function votes()
    {
        return $this->hasMany(SuggestionVote::class);
    }
}
