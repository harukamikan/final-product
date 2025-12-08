<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MileHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mission_id',
        'miles',
        'type',
        'description',
    ];

    protected $casts = [
        'miles' => 'integer',
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
}
