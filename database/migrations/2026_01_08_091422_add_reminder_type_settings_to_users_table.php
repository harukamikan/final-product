<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 期限リマインドのON/OFF
            $table->boolean('reminder_deadline_enabled')->default(true)->after('reminder_enabled');
            
            // 週次リマインドのON/OFF
            $table->boolean('reminder_weekly_enabled')->default(true)->after('reminder_deadline_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_deadline_enabled', 'reminder_weekly_enabled']);
        });
    }
};