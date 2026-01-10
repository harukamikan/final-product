<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Models\MileHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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
        $previousMiles = $user->total_miles ?? MileHistory::where('user_id', $user->id)->sum('miles');

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
            $userMission->save();
             // 個人ミッション（user_id がある）なら、ポイント加算
            if ($mission->user_id) {
                $user->increment('personal_mission_points', 1);
            }

            // マイル履歴作成
            MileHistory::create([
                'user_id'     => $user->id,
                'company_id'  => $user->company_id,
                'mission_id'  => $mission->id,
                'miles'       => $earned,
                'type'        => 'earn',
                'description' => 'mission_completed',
            ]);

            // ユーザーの合計マイルを更新
            $user->increment('total_miles', $earned);

            //達成ミッション数を加算
            $user->increment('completed_missions');
        });

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
        
        $oldRank = $rankInfo['old_rank'];
        $newRank = $rankInfo['new_rank'];
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
}
