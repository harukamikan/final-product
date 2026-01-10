<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            // 関連する個人ミッションID
            $table->unsignedBigInteger('related_personal_mission_id')
                ->nullable()
                ->after('mission_id');
            
            // 外部キー制約
            $table->foreign('related_personal_mission_id')
                ->references('id')
                ->on('personal_missions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            $table->dropForeign(['related_personal_mission_id']);
            $table->dropColumn('related_personal_mission_id');
        });
    }
};