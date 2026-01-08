<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 期限リマインドのタイミング（日数）
            $table->integer('reminder_days_before')->default(3)->after('reminder_enabled');
            
            // 週次リマインドの曜日（0=日曜, 1=月曜, ..., 6=土曜）
            $table->integer('reminder_day_of_week')->default(1)->after('reminder_days_before');
            
            // 週次リマインドの時間（0-23時）
            $table->integer('reminder_hour')->default(9)->after('reminder_day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_days_before', 'reminder_day_of_week', 'reminder_hour']);
        });
    }
};
