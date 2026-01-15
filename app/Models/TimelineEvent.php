<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class TimelineEvent extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'event_type',
        'source',
        'external_id',
        'occurred_at',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 会社IDでフィルタリング
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * イベントタイプでフィルタリング
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * キーワードで検索（payload内のタイトル、詳細、URLなど）
     */
    public function scopeSearchKeyword($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            // JSON検索: payload->title, payload->details, payload->url, payload->summary
            $q->whereRaw("JSON_EXTRACT(payload, '$.title') LIKE ?", ["%{$keyword}%"])
              ->orWhereRaw("JSON_EXTRACT(payload, '$.details') LIKE ?", ["%{$keyword}%"])
              ->orWhereRaw("JSON_EXTRACT(payload, '$.url') LIKE ?", ["%{$keyword}%"])
              ->orWhereRaw("JSON_EXTRACT(payload, '$.summary') LIKE ?", ["%{$keyword}%"]);
        });
    }

    /**
     * 特定のユーザーでフィルタリング
     */
    public function scopeForUser($query, $userId)
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }
}
