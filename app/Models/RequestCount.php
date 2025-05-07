<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestCount extends Model
{
    protected $fillable = ['method','endpoint','code','count'];
    public $timestamps = true;
}
