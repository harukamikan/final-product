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
        // 期限が近い半期目標を取得
        $upcomingGoals = SemesterGoal::where('deadline', '<=', $now->copy()->addDays(3))
            ->where('deadline', '>', $now)
            ->get();
        
        foreach ($upcomingGoals as $goal) {
            $this->sendDeadlineReminder($goal);
        }
        
        // ミッションには期限がないので、週次リマインドのみ
    }
    
    protected function checkWeeklyReminders($now)
    {
        // 月曜日の9時かチェック
        if ($now->isMonday() && $now->hour == 9) {
            // 全ユーザーの進行中の半期目標を取得
            $activeGoals = SemesterGoal::where('is_current', true)->get();
            
            foreach ($activeGoals as $goal) {
                $this->sendWeeklyReminder($goal);
            }
        }
    }
    
    protected function sendDeadlineReminder($target)
    {
        $slackService = app(SlackService::class);
        
        $deadline = Carbon::parse($target->deadline);
        $daysLeft = Carbon::now()->diffInDays($deadline);
        
        $message = "⏰ **期限リマインド**\n";
        $daysLeft = (int) Carbon::now()->diffInDays($deadline);
        $message .= "期限まで: あと{$daysLeft}日\n";
        
        // Slack通知を送信
        $slackService->sendMessage($message);
        
        $this->info("Sent deadline reminder for: {$target->title}");
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
