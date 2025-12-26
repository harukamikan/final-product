<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteToken extends Model
{
    protected $fillable = ['company_id','token','expires_at','max_uses','used_count'];
    protected $casts = ['expires_at' => 'datetime'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canUse(): bool
    {
        if ($this->isExpired()) return false;
        if ($this->max_uses === 0) return true;
        return $this->used_count < $this->max_uses;
    }
}
