<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Activity;
use App\Helpers\RankHelper;

class MissionService
{
    /**
     * 何かイベントが起きたときに呼ばれるメソッド（共通の入口）
     *
     * @param User   $user
     * @param string $triggerType  // 'tech_blog_posted' など
     * @param array  $payload      // ['mission_key' => ..., 'url' => ...] など
     *
     * @return array 達成データ
     */
    public function handleTrigger(User $user, string $triggerType, array $payload = []): array
    {
        $query = Mission::where('trigger_type', $triggerType);

        // mission_id が指定されていたら、その特定のミッションだけ対象にする（最優先）
        if (!empty($payload['mission_id'])) {
            $query->where('id', $payload['mission_id']);
        }
        // mission_key が指定されていたら、特定のミッションだけ対象にする
        elseif (!empty($payload['mission_key'])) {
            $query->where('key', $payload['mission_key']);
        }

        $missions = $query->get();

        $achievementData = [
            'earned_miles' => 0,
            'mission_completed' => false,
            'mission_title' => '',
            'progress' => ['current' => 0, 'required' => 1],
            'rank_info' => [],
            'next_action' => null,
        ];

        foreach ($missions as $mission) {
            $result = $this->progressMission($user, $mission, $payload);

            // 最後に処理したミッションの情報を保持
            if ($result['earned_miles'] > 0 || $result['progress']['current'] > 0) {
                $achievementData = $result;
            }
        }

        return $achievementData;
    }

    /**
     * ミッションを1ステップ進めて、条件を満たしたらクリア＋マイル付与
     *
     * @return array 達成データ
     */
    protected function progressMission(User $user, Mission $mission, array $payload = []): array
    {
        \Log::info('★ progressMission called', [
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'mission_title' => $mission->title,
        ]);
        // ユーザーのミッション状態レコードを取得 or 作成
        $userMission = UserMission::firstOrCreate(
            ['user_id' => $user->id, 'mission_id' => $mission->id],
            ['progress_count' => 0]
        );

        // ★ URL が来ていたら proof_url に保存（最新のものに更新）
        if (!empty($payload['url'])) {
            $userMission->proof_url = $payload['url'];
        }

        // 現在のマイル数（ランク計算用）
        $previousMiles = MileHistory::where('user_id', $user->id)->sum('miles');

        // すでにクリア済みで、repeatable じゃない場合は何もしない
        if ($userMission->isCompleted() && !$mission->repeatable) {
            // ただし proof_url だけは更新される可能性があるので save しておく
            $userMission->save();

            return [
                'earned_miles' => 0,
                'mission_completed' => false,
                'mission_title' => $mission->title,
                'progress' => [
                    'current' => $userMission->progress_count,
                    'required' => $mission->required_count,
                ],
                'rank_info' => RankHelper::getRankInfo($previousMiles, $previousMiles),
                'next_action' => $this->getNextAction($user),
            ];
        }

        // 進捗を1進める
        $userMission->progress_count += 1;
        
        // ★【追加】企業ミッション進捗時に、関連する個人ミッションも更新
        // ★【修正】企業ミッション完了時に、カテゴリで個人ミッションも更新
        $this->updatePersonalMissionByCategory($user, $mission);

        // まだ達成していない or すでに completed_at が入っている場合 → 進捗だけ保存
        if ($userMission->progress_count < $mission->required_count || $userMission->isCompleted()) {
            $userMission->save();

            return [
                'earned_miles' => 0,
                'mission_completed' => false,
                'mission_title' => $mission->title,
                'progress' => [
                    'current' => $userMission->progress_count,
                    'required' => $mission->required_count,
                ],
                'rank_info' => RankHelper::getRankInfo($previousMiles, $previousMiles),
                'next_action' => null,
            ];
        }

        // ここに来たら「今ちょうど達成した」
        $earned = (int) $mission->reward_miles;
       

        DB::transaction(function () use ($user, $mission, $userMission, $earned, $payload) {
            // 念のためトランザクション内でも URL を反映
            if (!empty($payload['url'])) {
                $userMission->proof_url = $payload['url'];
            }

            $userMission->completed_at = Carbon::now();
            $userMission->completion_count += 1;  // ★ 完了回数を加算
            $userMission->save();

            // ★ repeatable なら completed_at と progress_count をリセット（また完了できるように）
            if ($mission->repeatable) {
                $userMission->completed_at = null;
                $userMission->progress_count = 0;
                $userMission->save();
            }
            
            // 個人ミッション（user_id がある）なら、ポイント加算
            if ($mission->user_id) {
                $user->increment('personal_mission_points', 1);
            }

            // マイル履歴作成
            MileHistory::create([
                'user_id'     => $user->id,
                'company_id'  => $user->company_id,
                'mission_id'  => $mission->id,
                'semester_id' => \App\Models\SemesterSetting::current()->id,
                'miles'       => $earned,
                'type'        => 'earn',
                'description' => 'mission_completed',
            ]);

            // ユーザーの合計マイルを更新
            $user->increment('total_miles', $earned);

            //達成ミッション数を加算
            $user->increment('completed_missions');
            
        });

        // Activity を記録する
        Activity::create([
            'user_id'    => $user->id,
            'company_id' => $user->company_id,
            'type'       => 'mission',
            'title'      => 'ミッション達成：' . $mission->title,
            'date'       => now(),
            'url'        => $payload['url'] ?? null,
        ]);

        // 達成後のマイル数
        $currentMiles = $previousMiles + $earned;

        // ランク情報を取得
        $rankInfo = RankHelper::getRankInfo($currentMiles, $previousMiles);

        // ランクアップしたら Slack 通知
        if ($rankInfo['rank_up'] && $user->slack_id) {
            $this->sendRankUpNotification($user, $rankInfo);
        }

        // 次のアクションを取得
        $nextAction = $this->getNextAction($user);

        return [
            'earned_miles' => $earned,
            'mission_completed' => true,
            'mission_title' => $mission->title,
            'progress' => [
                'current' => $userMission->progress_count,
                'required' => $mission->required_count,
            ],
            'rank_info' => $rankInfo,
            'next_action' => $nextAction,
        ];
    }

    /**
     * 次のおすすめアクションを取得
     */
    protected function getNextAction(User $user): ?array
    {
        // 未着手のミッションを取得（報酬が高い順）
        $nextMission = Mission::whereNotIn(
            'id',
            UserMission::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->pluck('mission_id')
        )
            ->availableForUser($user->id)
            ->orderBy('reward_miles', 'desc')
            ->first();

        if (!$nextMission) {
            return null;
        }

        // ミッションタイプに応じたURLを生成
        $url = match ($nextMission->key) {
            'write_tech_blog' => route('missions.blog-url.form'),
            'event_speaker', 'event_organizer', 'acquire_certificate'
            => route('missions.form.create', ['mission' => $nextMission->id]),
            default => route('missions.index'),
        };

        return [
            'title' => $nextMission->title,
            'url' => $url,
        ];
    }

    public function completeManually(User $user, Mission $mission): array
    {
        // trigger_type が manual系かどうかを一応チェックしても良い
        if (!str_starts_with($mission->trigger_type, 'manual')) {
            // 必要ならここで例外など
            // throw new \RuntimeException('このミッションは手動達成ではありません。');
        }

        return $this->progressMission($user, $mission);
    }

    /**
     * ランクアップ通知を送信
     */
    protected function sendRankUpNotification(User $user, array $rankInfo): void
    {
        $slackService = app(SlackService::class);

        $oldRank = $rankInfo['previous_rank'];
        $newRank = $rankInfo['current_rank'];
        $currentMiles = $rankInfo['current_miles'];

        $oldEmoji = $oldRank == 'ゴールド' ? '🥇' : ($oldRank == 'シルバー' ? '🥈' : '🥉');
        $newEmoji = $newRank == 'ゴールド' ? '🥇' : ($newRank == 'シルバー' ? '🥈' : '🥉');

        $message = "🎉 ランクアップ！\n\n";
        $message .= "おめでとうございます！\n";
        $message .= "{$oldEmoji} {$oldRank} → {$newEmoji} {$newRank}\n\n";
        $message .= "現在のマイル: {$currentMiles}マイル\n";

        if ($newRank == 'ゴールド') {
            $message .= "最高ランク達成です！";
        } else {
            $nextRank = $newRank == 'シルバー' ? 'ゴールド' : 'シルバー';
            $nextMiles = $newRank == 'シルバー' ? 500 : 200;
            $remaining = $nextMiles - $currentMiles;
            $message .= "\n次は {$nextRank} を目指そう！\n";
            $message .= "あと {$remaining} マイル";
        }

        // Slack DM送信
        $slackService->sendDM($user->slack_id, $message);
    }

        /**
         * 関連する個人ミッションの進捗を更新
         */
        private function updateRelatedPersonalMission(User $user, int $personalMissionId): void
        {
            $personalMission = \App\Models\PersonalMission::find($personalMissionId);
            
            
            
            if (!$personalMission) {
                return;
            }
            
            // personal_missions の progress_count を直接更新
            $personalMission->increment('progress_count');
            
            
            
            // 達成したか確認
            if ($personalMission->progress_count >= $personalMission->required_count) {
                $personalMission->update(['completed_at' => Carbon::now()]);
                $user->increment('personal_mission_points', 1);
            }

        }
        /**
         * カテゴリ（キーワード）で個人ミッションを自動マッチングして更新
         */
        private function updatePersonalMissionByCategory(User $user, Mission $mission): void
        {
            // linked_category が企業ミッションの key と一致する個人ミッションを検索
            $personalMission = \App\Models\PersonalMission::where('user_id', $user->id)
                ->where('linked_category', $mission->key)
                ->whereNull('completed_at')
                ->first();
            
            if (!$personalMission) {
                return;
            }
            
            // 進捗+1
            $personalMission->increment('progress_count');
            $personalMission->refresh();  // ← 追加！最新の値を取得
            
            // 達成したか確認
            if ($personalMission->progress_count >= $personalMission->required_count) {
                $personalMission->update(['completed_at' => Carbon::now()]);
                $user->increment('personal_mission_points', 1);
            }
        }
            
          
}
