<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
        });

        // 既存データに対して、ユーザーのcompany_idを設定
        // SQLite互換の書き方に変更
        $semesterGoals = DB::table('semester_goals')
            ->whereNull('company_id')
            ->get();

        foreach ($semesterGoals as $goal) {
            $user = DB::table('users')->find($goal->user_id);
            if ($user) {
                DB::table('semester_goals')
                    ->where('id', $goal->id)
                    ->update(['company_id' => $user->company_id]);
            }
        }

        // NOT NULL制約と外部キー制約を追加
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semester_goals', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
