<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    // テーブル名が "goals" ならこの行は不要ですが、
    // 違う名前ならここをその名前にしてください
    protected $table = 'goals';

    // 変更可能なカラム
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
    ];
}
