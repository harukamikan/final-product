<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mile_histories', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('user_id')->constrained('semester_settings')->nullOnDelete();
        });

        // 既存データに現在の半期IDを設定
        $currentSemester = \App\Models\SemesterSetting::first();
        if ($currentSemester) {
            \App\Models\MileHistory::whereNull('semester_id')->update(['semester_id' => $currentSemester->id]);
        }
    }

    public function down(): void
    {
        Schema::table('mile_histories', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};
