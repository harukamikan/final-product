<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'target_id',
        'target_type',
        'days_before',
        'schedule',
        'notify_slack',
        'is_active',
        'last_sent_at',
        'next_send_at',
    ];

    protected $casts = [
        'notify_slack' => 'boolean',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'next_sent_at' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ポリモーフィックリレーション（target_id と target_type を使う）
    public function target()
    {
        return $this->morphTo();
    }
}
