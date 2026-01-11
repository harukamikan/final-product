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

        // 期限リマインドONのユーザーを取得
        $users = \App\Models\User::where('reminder_enabled', true)
            ->where('reminder_deadline_enabled', true)
            ->get();

        \Log::info('Found deadline users: ' . $users->count());
        
        foreach ($users as $user) {
            // ユーザーごとのリマインド日数を使用
            $reminderDate = $now->copy()->addDays($user->reminder_days_before);
            
            // 該当する期限の半期目標を取得
            $upcomingGoals = SemesterGoal::where('user_id', $user->id)
                ->whereDate('deadline', '=', $reminderDate->toDateString())
                ->get();
            
            foreach ($upcomingGoals as $goal) {
                $this->sendDeadlineReminderToUser($goal);
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
                $shouldSend = ($now->hour == $user->reminder_hour);
            } else {
                $reminderDays = json_decode($user->reminder_days, true) ?? [];
                $shouldSend = in_array($now->dayOfWeek, $reminderDays) && ($now->hour == $user->reminder_hour);
            }
            
            \Log::info("shouldSend: " . ($shouldSend ? 'true' : 'false'));
            
            if ($shouldSend) {
                $this->sendWeeklyReminder($user);
            }
        }
    }
    
    protected function sendDeadlineReminderToUser($goal)
    {
        // ユーザーにSlack IDが設定されていない場合はスキップ
        if (!$goal->user || !$goal->user->slack_id) {
            $this->info("Skipped (no Slack ID): {$goal->title}");
            return;
        }
        
        $slackService = app(SlackService::class);
        
        $deadline = Carbon::parse($goal->deadline);
        $daysLeft = (int) Carbon::now()->diffInDays($deadline);
        
        $message = "⏰ 期限リマインド\n\n";
        $message .= "目標: {$goal->title}\n";
        $message .= "期限まで: あと{$daysLeft}日\n\n";
        $message .= "頑張りましょう！💪";
        
        // ユーザーのSlackボットにDM送信
        $slackService->sendDM($goal->user->slack_id, $message);
        
        $this->info("Sent deadline reminder to {$goal->user->name}: {$goal->title}");
    }
    
    protected function sendWeeklyReminder($user)
    {
        $slackService = app(SlackService::class);
        
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
        
        // 最もマイルが高い未完了ミッション取得
        $topMission = \App\Models\Mission::whereNotIn('id', function($query) use ($user) {
            $query->select('mission_id')
                ->from('user_missions')
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at');
        })->orderBy('reward_miles', 'desc')->first();
        
        // メッセージ作成
        $message = "📊 今週の進捗レポート\n\n";
        
        if ($activeMissions->isNotEmpty()) {
            $message .= "▼ 進行中のミッション\n";
            foreach ($activeMissions as $userMission) {
                $progress = $userMission->progress_count;
                $required = $userMission->mission->required_count;
                $remaining = $required - $progress;
                $message .= "・{$userMission->mission->title} → {$progress}/{$required}件完了（残り{$remaining}件）\n";
            }
            $message .= "\n";
        }
        
        $message .= "▼ あなたのランク\n";
        $rankEmoji = $rank == 'ゴールド' ? '🥇' : ($rank == 'シルバー' ? '🥈' : '🥉');
        $message .= "現在: {$rankEmoji} {$rank}（{$totalMiles}マイル）\n";
        
        if ($rank != 'ゴールド') {
            $message .= "{$nextRank}まであと {$remaining} マイル\n\n";
        } else {
            $message .= "最高ランク達成です！\n\n";
        }
        
        if ($topMission) {
            $message .= "💡 次のおすすめミッション\n";
            $message .= "{$topMission->title}\n";
            $message .= "+{$topMission->reward_miles} マイル獲得\n\n";
        }
        
        $message .= "頑張りましょう！💪";
        
        // Slack DM送信
        $slackService->sendDM($user->slack_id, $message);
        
        $this->info("Sent weekly progress report to {$user->name}");
    }
}
