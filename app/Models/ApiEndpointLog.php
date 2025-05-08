<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpointLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'endpoint',
        'user_id',
        'user_name',
        'api_key_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];
}
