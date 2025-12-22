<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class Activity extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'date',
        'url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}