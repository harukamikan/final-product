<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToCompany;

class Mission extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'key',
        'title',
        'description',
        'trigger_type',
        'required_count',
        'reward_miles',
        'mile_min',
        'mile_max',
        'repeatable',
    ];

    protected $casts = [
        'reward_miles' => 'integer',
        'mile_min' => 'integer',
        'mile_max' => 'integer',
    ];

    /**
     * UserMission（ユーザーごとの進捗）とのリレーション
     * 例: $mission->userMissions
     */
    public function userMissions()
    {
        return $this->hasMany(UserMission::class);
    }
    /**
     * このミッションに付けられたタグ
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'mission_tag');
    }
    /**
     * ミッション作成者（個人ミッションの場合）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 特定のユーザーの達成状況を取得（便利メソッド）
     * 例: $mission->statusForUser($user->id)
     */
    public function statusForUser($userId)
    {
        return $this->userMissions()
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 特定ユーザーが見えるミッションのみ取得
     * （自分専用 または 共有ミッション）
     */
    public function scopeAvailableForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')  // 共有ミッション
              ->orWhere('user_id', $userId);  // 自分専用ミッション
        });
    }

    /**
     * 個人専用ミッションのみ
     */
    public function scopePersonalOnly($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 共有ミッション（user_id が null）のみ
     */
    public function scopeSharedOnly($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Check if this mission uses AI-based scoring
     */
    public function usesAiScoring(): bool
    {
        return !is_null($this->mile_min) && !is_null($this->mile_max);
    }
}
