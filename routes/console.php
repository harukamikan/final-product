<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// リマインド機能のスケジュール（テスト用：毎分）
Schedule::command('reminders:send')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->appendOutputTo('/proc/1/fd/1');

// 半期リセットの自動チェック（毎日0時に実行）
Schedule::call(function () {
    $today = now()->toDateString();
    
    // 終了日を過ぎた半期を持つ会社を取得
    $expiredSemesters = \App\Models\SemesterSetting::where('end_date', '<', $today)
        ->where('auto_reset_enabled', true)
        ->get();
    
    foreach ($expiredSemesters as $semester) {
        // その会社の最新の半期かどうか確認
        $latestSemester = \App\Models\SemesterSetting::where('company_id', $semester->company_id)
            ->orderBy('start_date', 'desc')
            ->first();
        
        // 最新の半期が期限切れなら新しい半期を作成
        if ($latestSemester && $latestSemester->id === $semester->id) {
            \Log::info("Auto reset for company {$semester->company_id}");
            Artisan::call('semester:reset', ['company_id' => $semester->company_id]);
        }
    }
})->daily()->at('00:00')->appendOutputTo('/proc/1/fd/1');
