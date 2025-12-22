<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToCompany;

class MileHistory extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'mission_id',
        'miles',
        'type',
        'description',
    ];

    protected $casts = [
        'miles' => 'integer',
        'created_at' => 'datetime',
    ];


    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ミッションとのリレーション（任意）
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
