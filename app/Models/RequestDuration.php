<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDuration extends Model
{
    protected $fillable = ['method','endpoint','count','duration_sum'];
    public $timestamps = true;
}
