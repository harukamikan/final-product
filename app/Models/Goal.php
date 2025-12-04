<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'deadline',
        'category',
        'target_value',
        'current_value',
        'criteria',
        'memo',
    ];
}
