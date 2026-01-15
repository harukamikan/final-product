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
     * 
     * @param User $user
     * @param MissionForm|array $data MissionFormインスタンスまたは共通フォーマット配列
     * @return TimelineEvent
     */
    public function createEventHostingEvent(User $user, $data): TimelineEvent
    {
        // MissionFormインスタンスの場合は共通フォーマットに変換
        if ($data instanceof MissionForm) {
            $payload = [
                'source' => 'form',
                'external_id' => null,
                'title' => $data->title,
                'description' => $data->details,
                'started_at' => $data->occurred_on . ' 00:00:00',
                'ended_at' => null,
                'place' => null,
                'address' => null,
                'url' => $data->evidence_url,
                'owner' => null,
            ];
            $source = 'form';
            $externalId = null;
            $occurredAt = $data->occurred_on . ' 00:00:00';
        } else {
            // 共通フォーマット配列の場合はそのまま使用
            $payload = $data;
            $source = $data['source'] ?? 'form';
            $externalId = $data['external_id'] ?? null;
            $occurredAt = $data['started_at'] ?? now();
        }

        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'event_hosting',
            'source' => $source,
            'external_id' => $externalId,
            'occurred_at' => $occurredAt,
            'payload' => $payload,
        ]);
    }

    /**
     * イベント登壇のタイムラインイベントを作成
     * 
     * @param User $user
     * @param MissionForm|array $data MissionFormインスタンスまたは共通フォーマット配列
     * @return TimelineEvent
     */
    public function createEventSpeakingEvent(User $user, $data): TimelineEvent
    {
        // MissionFormインスタンスの場合は共通フォーマットに変換
        if ($data instanceof MissionForm) {
            $payload = [
                'source' => 'form',
                'external_id' => null,
                'title' => $data->title,
                'description' => $data->details,
                'started_at' => $data->occurred_on . ' 00:00:00',
                'ended_at' => null,
                'place' => null,
                'address' => null,
                'url' => $data->evidence_url,
                'owner' => null,
            ];
            $source = 'form';
            $externalId = null;
            $occurredAt = $data->occurred_on . ' 00:00:00';
        } else {
            // 共通フォーマット配列の場合はそのまま使用
            $payload = $data;
            $source = $data['source'] ?? 'form';
            $externalId = $data['external_id'] ?? null;
            $occurredAt = $data['started_at'] ?? now();
        }

        return TimelineEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'event_speaking',
            'source' => $source,
            'external_id' => $externalId,
            'occurred_at' => $occurredAt,
            'payload' => $payload,
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
            'source' => 'form',
            'external_id' => null,
            'occurred_at' => $form->occurred_on . ' 00:00:00',
            'payload' => [
                'source' => 'form',
                'external_id' => null,
                'title' => $form->title,
                'description' => $form->details,
                'started_at' => $form->occurred_on . ' 00:00:00',
                'ended_at' => null,
                'place' => null,
                'address' => null,
                'url' => $form->evidence_url,
                'owner' => null,
            ],
        ]);
    }
}
