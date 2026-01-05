<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'company_id',
        'acquired_at',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'expires_at'  => 'datetime',
        'used_at'     => 'datetime',
    ];

    // 関連
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 判定系（超重要）
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && !$this->isExpired();
    }
}
