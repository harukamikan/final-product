<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::table('reward_survey_answers', function (Blueprint $table) {
        // カラムがなければ追加
        if (!Schema::hasColumn('reward_survey_answers', 'reward_survey_id')) {
            $table->unsignedBigInteger('reward_survey_id')->nullable();
        }
        
        // 外部キー追加
        $table->foreign('reward_survey_id')
            ->references('id')
            ->on('reward_surveys')
            ->cascadeOnDelete();
    });
}

    public function down(): void
    {
        Schema::table('reward_survey_answers', function (Blueprint $table) {
            $table->dropForeign(['reward_survey_id']);
        });
    }
};
