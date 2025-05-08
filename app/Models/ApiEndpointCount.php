<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpointCount extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'endpoint';
    protected $keyType = 'string';

    protected $fillable = ['endpoint', 'count'];
}
