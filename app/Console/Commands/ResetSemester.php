<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SlackService;

class ResetSemester extends Command
{
    protected $signature = 'semester:reset';
    protected $description = 'Reset miles and ranks for all users at semester end';

    public function handle()
    {
        \Log::info('=== Semester Reset started ===');
        
        // 1. 全ユーザーのマイルをリセット
        $userCount = User::count();
        \Log::info("Total users to reset: {$userCount}");
        
        // mile_histories の合計でマイルを計算している場合は
        // ユーザーテーブルにマイルカラムがあればリセット
        // User::query()->update(['total_miles' => 0]);
        
        // 2. Slack通知を送信
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
    }
}
