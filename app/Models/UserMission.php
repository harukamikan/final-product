<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mission_id',
        'status',
        'proof_url',
        'completed_at',
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
}
