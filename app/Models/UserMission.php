<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Concerns\BelongsToCompany;

class UserMission extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'mission_id',
        'proof_url',
        'progress_count',
        'completed_at',
        'related_personal_mission_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ミッション
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
        public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }
}
