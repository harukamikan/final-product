<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class TimelineEvent extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'mission_id',
        'event_type',
        'occurred_at',
        'payload',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * ユーザーリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ミッションリレーション
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * 種別ごとのスコープ
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * イベントタイトルを取得（種別ごとに適切なフィールドを返す）
     */
    public function getTitleAttribute(): ?string
    {
        return $this->payload['title'] ?? $this->payload['name'] ?? null;
    }

    /**
     * イベントURLを取得
     */
    public function getUrlAttribute(): ?string
    {
        return $this->payload['url'] ?? null;
    }
}
