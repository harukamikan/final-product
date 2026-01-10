<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * このタグが付けられたミッション
     */
    public function missions()
    {
        return $this->belongsToMany(Mission::class, 'mission_tag');
    }
}