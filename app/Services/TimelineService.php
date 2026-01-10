<?php

namespace App\Services;

use App\Models\User;
use App\Models\QiitaArticle;
use App\Models\MissionForm;
use App\Models\TimelineEvent;

class TimelineService
{
    /**
     * Qiita記事のタイムラインイベントを作成
     */
    public function createQiitaEvent(User $user, QiitaArticle $article): TimelineEvent
    {
        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'qiita',
            'occurred_at' => $article->posted_at ?? $article->created_at,
            'payload' => [
                'title' => $article->title,
                'url' => $article->url,
                'tags' => $article->tags ?? [],
                'likes_count' => $article->likes_count,
                'summary' => $article->summary,
                'posted_at' => $article->posted_at?->toDateString(),
                'item_id' => $article->item_id,
            ],
        ]);
    }

    /**
     * イベント企画・開催のタイムラインイベントを作成
     */
    public function createEventHostingEvent(User $user, MissionForm $form): TimelineEvent
    {
        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'event_hosting',
            'occurred_at' => $form->occurred_on . ' 00:00:00',
            'payload' => [
                'title' => $form->title,
                'occurred_on' => $form->occurred_on,
                'details' => $form->details,
                'evidence_url' => $form->evidence_url,
            ],
        ]);
    }

    /**
     * イベント登壇のタイムラインイベントを作成
     */
    public function createEventSpeakingEvent(User $user, MissionForm $form): TimelineEvent
    {
        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'event_speaking',
            'occurred_at' => $form->occurred_on . ' 00:00:00',
            'payload' => [
                'title' => $form->title,
                'occurred_on' => $form->occurred_on,
                'details' => $form->details,
                'evidence_url' => $form->evidence_url,
            ],
        ]);
    }

    /**
     * 資格取得のタイムラインイベントを作成
     */
    public function createCertificationEvent(User $user, MissionForm $form): TimelineEvent
    {
        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'certification',
            'occurred_at' => $form->occurred_on . ' 00:00:00',
            'payload' => [
                'title' => $form->title,
                'occurred_on' => $form->occurred_on,
                'details' => $form->details,
                'evidence_url' => $form->evidence_url,
            ],
        ]);
    }
}
