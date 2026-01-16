<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminSetting extends Model
{
    protected $fillable = [
        'company_id',
        'notification_type',
        'slack_id',
        'email',
        'personal_mission_edit_start',
        'personal_mission_edit_end',
    ];

    protected $casts = [
        'personal_mission_edit_start' => 'date',
        'personal_mission_edit_end' => 'date',
    ];
}