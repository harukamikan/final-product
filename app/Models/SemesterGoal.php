<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterGoal extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'title',
        'deadline',
        'semester',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}