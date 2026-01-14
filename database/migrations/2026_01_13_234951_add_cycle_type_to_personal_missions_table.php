<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->enum('cycle_type', ['none', 'weekly', 'monthly'])->default('none')->after('repeatable');
            $table->unsignedInteger('cycle_streak')->default(0)->after('cycle_type');  // 連続達成数
            $table->date('cycle_last_completed_at')->nullable()->after('cycle_streak');  // 最後に達成したサイクル
        });
    }

    public function down(): void
    {
        Schema::table('personal_missions', function (Blueprint $table) {
            $table->dropColumn(['cycle_type', 'cycle_streak', 'cycle_last_completed_at']);
        });
    }
};