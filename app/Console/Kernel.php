<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Artisan コマンド登録
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * スケジュール定義
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule
            ->command('rewards:deactivate-expired')
            ->everyMinute();

            // リマインド通知（テスト用：毎分実行）
            $schedule
                ->command('reminders:send')
                ->everyMinute();
    }
}
