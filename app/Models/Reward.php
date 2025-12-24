<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'reward_miles',
        'status',
    ];
}
