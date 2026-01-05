<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class SemesterGoal extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'category',
        'title',
        'description',
        'deadline',
        'is_current',
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
