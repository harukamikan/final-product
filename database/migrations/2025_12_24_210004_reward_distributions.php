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
        // テーブルが存在しない場合のみ作成
        if (!Schema::hasTable('reward_distributions')) {
            Schema::create('reward_distributions', function (Blueprint $table) {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('reward_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->unsignedInteger('quantity')->nullable();
                $table->boolean('is_active')->default(false);

                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_distributions');
    }
};
