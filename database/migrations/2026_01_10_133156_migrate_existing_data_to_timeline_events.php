<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 既存のQiita記事をtimeline_eventsに移行
        $qiitaArticles = DB::table('qiita_articles')->get();
        
        foreach ($qiitaArticles as $article) {
            DB::table('timeline_events')->insert([
                'company_id' => $article->company_id,
                'user_id' => $article->user_id,
                'event_type' => 'qiita',
                'occurred_at' => $article->posted_at ?? $article->created_at,
                'payload' => json_encode([
                    'title' => $article->title,
                    'url' => $article->url,
                    'tags' => json_decode($article->tags ?? '[]'),
                    'likes_count' => $article->likes_count,
                    'summary' => $article->summary,
                    'posted_at' => $article->posted_at ? date('Y-m-d', strtotime($article->posted_at)) : null,
                    'item_id' => $article->item_id,
                ]),
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
            ]);
        }

        // 既存のミッションフォームをtimeline_eventsに移行
        $missionForms = DB::table('mission_forms')->get();
        
        foreach ($missionForms as $form) {
            // categoryに応じてevent_typeを決定
            $eventType = match($form->category) {
                'event_host' => 'event_hosting',
                'event_speaker' => 'event_speaking',
                'acquire_certificate', 'certification' => 'certification',
                default => null,
            };

            // event_typeが決定できた場合のみ移行
            if ($eventType) {
                // user_idからcompany_idを取得
                $user = DB::table('users')->where('id', $form->user_id)->first();
                
                if ($user && $user->company_id) {
                    DB::table('timeline_events')->insert([
                        'company_id' => $user->company_id,
                        'user_id' => $form->user_id,
                        'event_type' => $eventType,
                        'occurred_at' => $form->occurred_on . ' 00:00:00',
                        'payload' => json_encode([
                            'title' => $form->title,
                            'occurred_on' => $form->occurred_on,
                            'details' => $form->details,
                            'evidence_url' => $form->evidence_url,
                        ]),
                        'created_at' => $form->created_at,
                        'updated_at' => $form->updated_at,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // タイムラインイベントを削除（ロールバック時）
        DB::table('timeline_events')->truncate();
    }
};
