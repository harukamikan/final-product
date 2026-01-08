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
        $now = Carbon::now();
        
        // 期限リマインドをチェック
        $this->checkDeadlineReminders($now);
        
        // 週次リマインドをチェック
        $this->checkWeeklyReminders($now);
        
        $this->info("Reminders checked and sent successfully!");
    }
    
    protected function checkDeadlineReminders($now)
    {
        // リマインド通知ONのユーザーを取得
        $users = \App\Models\User::where('reminder_enabled', true)->get();
        
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
        // リマインド通知ONのユーザーを取得
        $users = \App\Models\User::where('reminder_enabled', true)->get();
        
        foreach ($users as $user) {
            // ユーザーごとの設定曜日・時間をチェック
            if ($now->dayOfWeek == $user->reminder_day_of_week && $now->hour == $user->reminder_hour) {
                // ユーザーの進行中の半期目標を取得
                $activeGoals = SemesterGoal::where('user_id', $user->id)
                    ->where('is_current', true)
                    ->get();
                
                foreach ($activeGoals as $goal) {
                    $this->sendWeeklyReminder($goal);
                }
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
        
        $message = "⏰ **期限リマインド**\n";
        $message .= "目標: {$goal->title}\n";
        $message .= "期限まで: あと{$daysLeft}日\n";
        
        // ユーザーのSlackボットにDM送信
        $slackService->sendDM($goal->user->slack_id, $message);
        
        $this->info("Sent deadline reminder to {$goal->user->name}: {$goal->title}");
    }
    
    protected function sendWeeklyReminder($goal)
    {
        $slackService = app(SlackService::class);
        
        $message = "📅 **週次リマインド**\n";
        $message .= "今週の目標を確認しましょう！\n";
        $message .= "目標: {$goal->title}\n";
        
        // Slack通知を送信
        $slackService->sendMessage($message);
        
        $this->info("Sent weekly reminder for: {$goal->title}");
    }
}
