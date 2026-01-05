<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardSurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'reward_survey_id',
        'user_id',
        'company_id',
        'first_choice',
        'second_choice',
        'third_choice',
    ];

    public function survey()
    {
        return $this->belongsTo(RewardSurvey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
