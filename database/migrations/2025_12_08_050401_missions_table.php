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
        $table->string('code')->unique();   // 'WRITE_TECH_BLOG' みたいな識別子
        $table->string('title');            // 技術系ブログを1本書く
        $table->text('description')->nullable();
        $table->integer('reward_miles');    // 50 など
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
