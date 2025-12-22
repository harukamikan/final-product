<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','key', 'title', 'description',
        'trigger_type', 'required_count',
        'reward_miles', 'repeatable',
    ];

    protected $casts = [
        'reward_miles' => 'integer',
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
     * 特定のユーザーの達成状況を取得（便利メソッド）
     * 例: $mission->statusForUser($user->id)
     */
    public function statusForUser($userId)
    {
        return $this->userMissions()
            ->where('user_id', $userId)
            ->first();
    }
}
