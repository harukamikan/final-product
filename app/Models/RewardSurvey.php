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

    // 回答一覧
    public function answers()
    {
        return $this->hasMany(RewardSurveyAnswer::class);
    }

    // 受付中スコープ（便利）
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
