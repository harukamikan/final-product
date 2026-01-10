<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Company;
use App\Models\MileHistory;
use App\Models\RewardHistory;
use App\Models\UserMission;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'slack_id',
        'reminder_enabled',
        'background_type',
        'background_value',
        'nickname',
        'reminder_days_before',
        'reminder_day_of_week',
        'reminder_hour',
        'reminder_deadline_enabled',
        'reminder_weekly_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarded_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function missions()
    {
        return $this->hasMany(UserMission::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rewardHistories()
    {
        return $this->hasMany(RewardHistory::class);
    }

    public function mileHistories()
    {
        return $this->hasMany(MileHistory::class);
    }

    public function getTotalMilesAttribute(): int
    {
        return $this->mileHistories()->sum('miles');
    }

    public function rewardSurviveyAnswers()
    {
        return $this->hasMany(RewardSurveyAnswer::class);
    }

    public function onboardingSurvey()
    {
        return $this->hasOne(OnboardingSurvey::class);
    }

    public function semesterGoals()
    {
        return $this->hasMany(SemesterGoal::class);
    }
}
