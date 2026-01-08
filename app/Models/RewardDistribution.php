<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RewardDistribution extends Model
{
    protected $fillable = [
        'company_id',
        'reward_id',
        'quantity',
        'starts_at',
        'ends_at',
        'reward_expires_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at'         => 'datetime',
        'ends_at'           => 'datetime',
        'reward_expires_at' => 'datetime',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->isPast();
    }
}
