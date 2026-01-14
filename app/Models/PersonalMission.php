<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PersonalMission extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'key',
        'linked_category',
        'title',
        'description',
        'trigger_type',
        'required_count',
        'progress_count',
        'completed_at',
        'reward_miles',
        'repeatable',
        'cycle_type',
        'cycle_streak',
        'cycle_last_completed_at',
    ];
    protected $casts = [
        'completed_at' => 'datetime',
        'cycle_last_completed_at' => 'date',
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