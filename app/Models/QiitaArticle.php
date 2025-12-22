<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class QiitaArticle extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'mission_id',
        'item_id',
        'title',
        'body',
        'tags',
        'likes_count',
        'posted_at',
        'url',
    ];

    protected $casts = [
        'tags'      => 'array',
        'posted_at' => 'datetime',
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
