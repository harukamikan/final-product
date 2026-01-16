<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Models\Mission;
use App\Models\SemesterGoal;
use App\Services\SlackService;
use Carbon\Carbon;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send scheduled reminders to users';

    public function handle()
    {
        \Log::info('=== SendReminders started ===');
        \Log::info('Bot token exists: ' . (config('services.slack.notifications.bot_user_oauth_token') ? 'YES' : 'NO'));

        try {
            $userCount = \App\Models\User::count();
            \Log::info('DB OK - Total users: ' . $userCount);
            
            // リマインド対象ユーザーを確認
            $reminderUsers = \App\Models\User::where('reminder_enabled', true)
                ->where('reminder_weekly_enabled', true)
                ->get();
            \Log::info('Reminder enabled users: ' . $reminderUsers->count());
            
            foreach ($reminderUsers as $user) {
                \Log::info("User: {$user->name}, slack_id: {$user->slack_id}, hour: {$user->reminder_hour}, freq: {$user->reminder_frequency}");
            }
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return;
        }

        $now = Carbon::now();
        \Log::info('Current time: ' . $now->toDateTimeString());
        \Log::info('Current hour: ' . $now->hour);
        
        // 期限リマインドをチェック
        $this->checkDeadlineReminders($now);
        
        // 週次リマインドをチェック
        $this->checkWeeklyReminders($now);
        
        $this->info("Reminders checked and sent successfully!");
    \Log::info('=== SendReminders finished ===');
    }
    
    protected function checkDeadlineReminders($now)
    {
        \Log::info('=== checkDeadlineReminders ===');

        // 朝9時以外は何もしない
        if ($now->hour != 9) {
            return;
        }
        
        // 期限リマインドONのユーザーを取得
        $users = \App\Models\User::where('reminder_enabled', true)
            ->where('reminder_deadline_enabled', true)
            ->get();

        \Log::info('Found deadline users: ' . $users->count());
        
        foreach ($users as $user) {
            $goals = SemesterGoal::where('user_id', $user->id)
                ->whereNotNull('deadline')
                ->get();
            
            foreach ($goals as $goal) {
                $daysLeft = (int) $now->copy()->startOfDay()->diffInDays(Carbon::parse($goal->deadline)->startOfDay(), false);
                
                // 期限が過ぎてたらスキップ
                if ($daysLeft <= 0) {
                    continue;
                }
                
                // 1日前 → 全員に送信
                if ($daysLeft == 1) {
                    $this->sendDeadlineReminderToUser($goal, $daysLeft);
                    continue;
                }
                
                // 月初め（1日）→ 全員に送信
                if ($now->day == 1) {
                    $this->sendDeadlineReminderToUser($goal, $daysLeft);
                    continue;
                }
                
                // ユーザーが選んだ日（7日前 / 5日前 / 3日前）
                if ($daysLeft == $user->reminder_days_before) {
                    $this->sendDeadlineReminderToUser($goal, $daysLeft);
                }
            }
        }
    }

    protected function checkWeeklyReminders($now)
    {
        \Log::info('=== checkWeeklyReminders ===');
        
        // 週次リマインドONのユーザーを取得
        $users = \App\Models\User::where('reminder_enabled', true)
            ->where('reminder_weekly_enabled', true)
            ->get();
        
        \Log::info('Found weekly users: ' . $users->count());
        
        foreach ($users as $user) {
            \Log::info("User {$user->id}: hour={$user->reminder_hour}, now_hour={$now->hour}, freq={$user->reminder_frequency}");
            
            $shouldSend = false;
            
            if ($user->reminder_frequency === 'daily') {
                // 時間が一致 かつ 0〜29分の間のみ送信
                $shouldSend = ($now->hour == $user->reminder_hour && $now->minute < 30);
            } else {
                $reminderDays = json_decode($user->reminder_days, true) ?? [];
                // 曜日・時間が一致 かつ 0〜29分の間のみ送信
                $shouldSend = in_array($now->dayOfWeek, $reminderDays) 
                            && ($now->hour == $user->reminder_hour) 
                            && ($now->minute < 30);
            }
            
            \Log::info("shouldSend: " . ($shouldSend ? 'true' : 'false'));
            
            if ($shouldSend) {
                $this->sendWeeklyReminder($user);
            }
        }
    }
    
    protected function sendDeadlineReminderToUser($goal, $daysLeft = null)
    {
        if (!$goal->user) {
            return;
        }
        
        if ($daysLeft === null) {
            $deadline = Carbon::parse($goal->deadline);
            $daysLeft = (int) Carbon::now()->startOfDay()->diffInDays($deadline->startOfDay(), false);
        }
        
        $message = "目標: {$goal->title}\n期限まで: あと{$daysLeft}日\n頑張りましょう！💪";
        
        // アプリ内通知を保存
        \App\Models\Notification::create([
            'user_id' => $goal->user->id,
            'title' => '⏰ 期限リマインド',
            'type' => 'deadline_reminder',
            'message' => $message,
            'is_read' => false,
        ]);
        
        // Slack IDがあればSlackにも送信
        if ($goal->user->slack_id) {
            $slackService = app(SlackService::class);
            $slackMessage = "⏰ 期限リマインド\n\n" . $message;
            $slackService->sendDM($goal->user->slack_id, $slackMessage);
        }
        
        $this->info("Sent deadline reminder to {$goal->user->name}: {$goal->title}");
    }
    
    protected function sendWeeklyReminder($user)
    {
        // 進行中のミッション取得
        $activeMissions = \App\Models\UserMission::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('mission')
            ->get();
        
        // ランク情報取得
        $totalMiles = \App\Models\MileHistory::where('user_id', $user->id)->sum('miles');
        $rank = $totalMiles >= 500 ? 'ゴールド' : ($totalMiles >= 200 ? 'シルバー' : 'ブロンズ');
        $nextRank = $rank == 'ブロンズ' ? 'シルバー' : ($rank == 'シルバー' ? 'ゴールド' : '最高ランク');
        $nextMiles = $rank == 'ブロンズ' ? 200 : ($rank == 'シルバー' ? 500 : 500);
        $remaining = max(0, $nextMiles - $totalMiles);
        
        // 最もマイルが高い未完了ミッション取得（自社のミッションのみ）
        $topMission = \App\Models\Mission::where('company_id', $user->company_id)
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('mission_id')
                    ->from('user_missions')
                    ->where('user_id', $user->id)
                    ->whereNotNull('completed_at');
            })->orderBy('reward_miles', 'desc')->first();
        
        // メッセージ作成
        $message = "";
        
        if ($activeMissions->isNotEmpty()) {
            $message .= "▼ 進行中のミッション\n";
            foreach ($activeMissions as $userMission) {
                $progress = $userMission->progress_count ?? 0;
                $required = $userMission->mission->required_count ?? 1;
                $missionRemaining = $required - $progress;
                $message .= "・{$userMission->mission->title} → {$progress}/{$required}件完了（残り{$missionRemaining}件）\n";
            }
            $message .= "\n";
        }
        
        $rankEmoji = $rank == 'ゴールド' ? '🥇' : ($rank == 'シルバー' ? '🥈' : '🥉');
        $message .= "▼ あなたのランク\n";
        $message .= "現在: {$rankEmoji} {$rank}（{$totalMiles}マイル）\n";
        
        if ($rank != 'ゴールド') {
            $message .= "{$nextRank}まであと {$remaining} マイル\n\n";
        } else {
            $message .= "最高ランク達成です！\n\n";
        }
        
        if ($topMission) {
            $message .= "💡 次のおすすめミッション\n";
            $message .= "{$topMission->title}\n";
            $message .= "+{$topMission->reward_miles} マイル獲得";
        }
        
        // アプリ内通知を保存
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => '📊 週次レポート',
            'type' => 'weekly_reminder',
            'message' => $message,
            'is_read' => false,
        ]);
        
        // Slack IDがあればSlackにも送信
        if ($user->slack_id) {
            $slackService = app(SlackService::class);
            $slackMessage = "📊 今週の進捗レポート\n\n" . $message . "\n\n頑張りましょう！💪";
            $slackService->sendDM($user->slack_id, $slackMessage);
        }
        
        $this->info("Sent weekly progress report to {$user->name}");
    }
}
