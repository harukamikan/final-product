<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
