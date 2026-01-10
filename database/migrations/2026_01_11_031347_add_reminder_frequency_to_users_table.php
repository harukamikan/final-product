<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 新しいカラムを追加
            $table->string('reminder_frequency')->default('weekly')->after('reminder_hour'); // 'daily' or 'weekly'
            $table->json('reminder_days')->nullable()->after('reminder_frequency'); // [1,3,5] など
            
            // 古いカラムを削除
            $table->dropColumn('reminder_day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_frequency', 'reminder_days']);
            $table->integer('reminder_day_of_week')->default(1);
        });
    }
};
