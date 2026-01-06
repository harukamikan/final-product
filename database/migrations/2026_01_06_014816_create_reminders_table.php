<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'deadline' or 'weekly'
            $table->foreignId('target_id')->nullable(); // mission_id or semester_goal_id
            $table->string('target_type')->nullable(); // 'Mission' or 'SemesterGoal'
            
            // リマインド設定
            $table->integer('days_before')->nullable(); // 期限の何日前
            $table->string('schedule')->nullable(); // 'weekly_monday_09:00'
            
            // 通知方法
            $table->boolean('notify_slack')->default(true);
            
            // 状態管理
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_send_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
