<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// リマインド機能のスケジュール（テスト用：毎分）
Schedule::command('reminders:send')
    ->everyMinute()
    ->withoutOverlapping();
    