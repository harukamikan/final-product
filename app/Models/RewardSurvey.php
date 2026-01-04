<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /**
     * 回答受付中のアンケート
     */
    public function scopeAccepting($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_at')
                  ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>=', now());
            });
    }

    /**
     * 回答一覧
     */
    public function answers()
    {
        return $this->hasMany(RewardSurveyAnswer::class);
    }
}
