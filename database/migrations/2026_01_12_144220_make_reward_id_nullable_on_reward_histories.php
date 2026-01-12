<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('reward_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reward_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('reward_id')->nullable(false)->change();
        });
    }
};
