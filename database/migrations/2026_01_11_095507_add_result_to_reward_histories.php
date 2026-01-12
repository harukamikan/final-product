<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_histories', function (Blueprint $table) {
            $table->string('result')->nullable(); // win / lose / miles
            $table->integer('miles')->nullable(); // マイル結果用
        });
    }

    public function down(): void
    {
        Schema::table('reward_histories', function (Blueprint $table) {
            $table->dropColumn(['result', 'miles']);
        });
    }
};
