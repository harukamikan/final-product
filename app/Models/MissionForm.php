<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionForm extends Model
{
    protected $fillable = [
        'user_id',
        'mission_id',
        'category',
        'title',
        'occurred_on',
        'details',
        'evidence_url',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}
