<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SemesterSetting;
use App\Services\SlackService;

class ResetSemester extends Command
{
    protected $signature = 'semester:reset';
    protected $description = 'Reset miles and ranks for all users at semester end';

    public function handle()
    {
        \Log::info('=== Semester Reset started ===');
        
        // 1. completed_missions だけリセット（total_milesはアクセサなので不要）
        User::query()->update([
            'completed_missions' => 0,
        ]);
        
        \Log::info("All users reset: completed_missions=0");
        
        // 2. 新しい半期を作成
        $oldSemester = SemesterSetting::current();
        $newSemester = SemesterSetting::create([
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'auto_reset_enabled' => $oldSemester->auto_reset_enabled,
        ]);
        
        \Log::info("New semester created: ID={$newSemester->id}");
        
        // 3. Slack通知を送信
        $slackService = app(SlackService::class);
        $users = User::whereNotNull('slack_id')->get();
        
        \Log::info("Users with Slack ID: {$users->count()}");
        
        foreach ($users as $user) {
            $message = "🔄 半期リセットが実行されました\n\n";
            $message .= "マイルとランクがリセットされました。\n";
            $message .= "新しい半期、頑張りましょう！💪";
            
            $slackService->sendDM($user->slack_id, $message);
            \Log::info("Sent reset notification to: {$user->name}");
        }
        
        $this->info('Semester reset completed successfully!');
        \Log::info('=== Semester Reset finished ===');
        
        return Command::SUCCESS;
    }
}
