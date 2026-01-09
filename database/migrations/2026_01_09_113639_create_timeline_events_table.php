<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            
            // 誰が投稿したか
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // どの会社に所属するか（会社内共有用）
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // どのミッションと紐づくか（任意）
            $table->foreignId('mission_id')->nullable()->constrained()->nullOnDelete();
            
            // イベント種別: qiita, event_hosting, event_speaking, certification
            $table->enum('event_type', ['qiita', 'event_hosting', 'event_speaking', 'certification']);
            
            // イベント発生日時（投稿日時、イベント開催日時、資格取得日等）
            $table->timestamp('occurred_at')->nullable();
            
            // 種別ごとの詳細情報を JSON で格納
            // - Qiita: item_id, title, url, tags, likes_count, summary
            // - Event: event_id, title, url, started_at, ended_at, place, accepted, limit, catch, api_fetched
            // - Certification: name, organization, acquired_at, score, difficulty, memo
            $table->json('payload');
            
            $table->timestamps();
            
            // インデックス（会社ごと、種別ごと、日時順で検索）
            $table->index(['company_id', 'event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
