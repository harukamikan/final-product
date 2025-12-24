<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardSurvey extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'first_choice',
        'second_choice',
        'third_choice',
        'status',
    ];
}
