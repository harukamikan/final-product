<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_survey_answers', function (Blueprint $table) {
            // すでに存在するカラムに対して FK だけ付与
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
