<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'reward_survey_active',
        'reward_survey_deadline',
    ];

    protected $casts = [
        'reward_survey_deadline' => 'datetime',
        'reward_survey_active'  => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
