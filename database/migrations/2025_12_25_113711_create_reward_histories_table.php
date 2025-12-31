<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('reward_histories')) {
            Schema::create('reward_histories', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('reward_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('via'); // gacha / scratch
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_histories');
    }
};
