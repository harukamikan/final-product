<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalMission extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'key',
        'title',
        'description',
        'trigger_type',
        'required_count',
        'reward_miles',
        'repeatable',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 会社とのリレーション
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ユーザー別の進捗
     */
    public function userMissions()
    {
        return $this->hasMany(UserMission::class, 'mission_id');
    }
}