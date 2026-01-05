<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingSurvey extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'role',
        'preferred_output',
        'current_situation',
        'experience_level',
        'segment',
    ];

    /**
     * Relationship to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
