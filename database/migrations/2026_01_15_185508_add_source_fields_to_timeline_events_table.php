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
        Schema::table('timeline_events', function (Blueprint $table) {
            // 入力経路を識別（connpass / form / qiita など）
            $table->string('source')->nullable()->after('event_type');
            
            // 外部サービスのID（Connpass event_id, Qiita item_id など）
            $table->string('external_id')->nullable()->after('source');
            
            // 重複防止のためのインデックス
            // external_idがnullの場合もあるので、複合インデックスとして設定
            $table->index(['user_id', 'event_type', 'source', 'external_id'], 'timeline_events_dedup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline_events', function (Blueprint $table) {
            $table->dropIndex('timeline_events_dedup_index');
            $table->dropColumn(['source', 'external_id']);
        });
    }
};
