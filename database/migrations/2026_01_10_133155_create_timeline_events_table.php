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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();

            // 会社ID（マルチテナント）
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // 誰が実施したか
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // イベントタイプ
            $table->enum('event_type', ['qiita', 'event_hosting', 'event_speaking', 'certification']);

            // イベント発生日時（フォーム入力日 or Qiita投稿日、なければcreated_at）
            $table->timestamp('occurred_at');

            // イベント詳細データ（JSON）
            // 例: {"title": "...", "url": "...", "tags": [...], ...}
            $table->json('payload');

            $table->timestamps();

            // タイムライン取得最適化のための複合インデックス
            $table->index(['company_id', 'occurred_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
