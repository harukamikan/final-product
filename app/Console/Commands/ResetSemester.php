<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SemesterSetting;
use App\Services\SlackService;

class ResetSemester extends Command
{
    protected $signature = 'semester:reset {company_id}';
    protected $description = 'Reset miles and ranks for all users in a company at semester end';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        \Log::info("=== Semester Reset started for company {$companyId} ===");
        
        // 1. その会社のユーザーだけ completed_missions をリセット
        $userCount = User::where('company_id', $companyId)->count();
        User::where('company_id', $companyId)->update([
            'completed_missions' => 0,
        ]);
        
        \Log::info("Reset {$userCount} users: completed_missions=0");
        
        // 2. その会社の新しい半期を作成
        $oldSemester = SemesterSetting::current($companyId);
        $newSemester = SemesterSetting::create([
            'company_id' => $companyId,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'auto_reset_enabled' => $oldSemester ? $oldSemester->auto_reset_enabled : true,
        ]);
        
        \Log::info("New semester created: ID={$newSemester->id}");
        
        // 3. その会社のユーザーにSlack通知を送信
        $slackService = app(SlackService::class);
        $users = User::where('company_id', $companyId)
            ->whereNotNull('slack_id')
            ->get();
        
        \Log::info("Users with Slack ID: {$users->count()}");
        
        foreach ($users as $user) {
            $message = "🔄 半期リセットが実行されました\n\n";
            $message .= "マイルとランクがリセットされました。\n";
            $message .= "新しい半期、頑張りましょう！💪";
            
            $slackService->sendDM($user->slack_id, $message);
            \Log::info("Sent reset notification to: {$user->name}");
        }
        
        $this->info('Semester reset completed successfully!');
        \Log::info("=== Semester Reset finished for company {$companyId} ===");
        
        return Command::SUCCESS;
    }
}
