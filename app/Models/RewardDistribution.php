<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardDistribution extends Model
{
    protected $fillable = [
        'company_id',
        'reward_id',
        'quantity',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
