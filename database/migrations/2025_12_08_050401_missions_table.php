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
        // 例: migration

    Schema::create('missions', function (Blueprint $table) {
        $table->id();
        $table->string('key'); // 'write_tech_blog' みたいな識別子
        $table->string('title');
        $table->text('description')->nullable();

        // どういうタイミングで達成されるミッションか（トリガー種別）
        $table->string('trigger_type'); // 'manual', 'tech_blog_posted', 'daily_login' など

        // 達成条件（単純な場合は回数）
        $table->unsignedInteger('required_count')->default(1);

        // 報酬マイル
        $table->unsignedInteger('reward_miles')->default(0);

        // 一度きりか、複数回達成可能か
        $table->boolean('repeatable')->default(false);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
