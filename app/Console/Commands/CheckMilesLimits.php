<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mission;
use App\Services\MissionLimitsService;

class CheckMilesLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missions:check-miles-limits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '既存ミッションの報酬マイルが設定範囲内にあるかチェックします';

    /**
     * Execute the console command.
     */
    public function handle(MissionLimitsService $limitsService): int
    {
        $this->info('🔍 ミッションのマイル範囲チェックを開始します...');
        $this->newLine();
        
        $missions = Mission::all();
        $violations = [];

        foreach ($missions as $mission) {
            try {
                $limits = $limitsService->getLimits($mission->key);
                
                if (!$limitsService->isWithinRange($mission->key, $mission->reward_miles)) {
                    $violations[] = [
                        'id' => $mission->id,
                        'key' => $mission->key,
                        'title' => $mission->title,
                        'current_miles' => $mission->reward_miles,
                        'allowed_range' => "{$limits['min_miles']} 〜 {$limits['max_miles']}",
                    ];
                }
            } catch (\InvalidArgumentException $e) {
                // 未定義のキーの場合は警告
                $this->warn("⚠️  未定義のミッションkey: {$mission->key} (ID: {$mission->id})");
            }
        }

        if (empty($violations)) {
            $this->info('✅ すべてのミッションが範囲内です。');
            return 0;
        }

        $this->warn("⚠️  {count($violations)} 件のミッションが範囲外です:");
        $this->newLine();
        
        $this->table(
            ['ID', 'Key', 'Title', 'Current Miles', 'Allowed Range'],
            array_map(fn($v) => array_values($v), $violations)
        );

        $this->newLine();
        $this->comment('💡 これらのミッションは管理画面から編集することで範囲内に修正できます。');

        return 1;
    }
}
