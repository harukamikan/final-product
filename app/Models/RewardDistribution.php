<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardDistribution extends Model
{
    protected $fillable = [
        'company_id',
        'reward_id',
        'quantity',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
