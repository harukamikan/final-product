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

    /**
     * 配布として期限切れかどうか
     */
    public function getIsExpiredAttribute(): bool
    {
        $now = now();

        // 配布終了日時が過ぎている
        if ($this->ends_at && $this->ends_at->isPast()) {
            return true;
        }

        // 報酬の有効期限（取得後）が過ぎている
        if ($this->reward_expires_at && $this->reward_expires_at->isPast()) {
            return true;
        }

        return false;
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where('is_active', true)

            // 配布開始
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })

            // 配布終了
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })

            // 期限切れ報酬を除外
            ->where(function ($q) {
                $q->whereNull('reward_expires_at')
                    ->orWhere('reward_expires_at', '>', now());
            })

            // 数量
            ->where(function ($q) {
                $q->whereNull('quantity')
                    ->orWhere('quantity', '>', 0);
            });
    }
}
