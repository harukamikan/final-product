<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id();

            // 所有者
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 報酬マスタ
            $table->foreignId('reward_id')
                ->constrained()
                ->cascadeOnDelete();

            // 会社（管理・絞り込み用）
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // 取得日時
            $table->timestamp('acquired_at')->nullable();

            // 有効期限（null = 期限なし）
            $table->timestamp('expires_at')->nullable();

            // 使用済み日時
            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            // よく使う条件に index
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_rewards');
    }
};
