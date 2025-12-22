<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class Goal extends Model
{
    use BelongsToCompany;
    
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
