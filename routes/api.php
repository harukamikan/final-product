<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlackController;

// Slack bot commands endpoint
Route::post('/slack/commands', [SlackController::class, 'commands']);

// 個人ミッション進捗確認
Route::post('/slack/my-missions', [SlackController::class, 'myMissions']);

// 報酬一覧
Route::post('/slack/my-rewards', [SlackController::class, 'myRewards']);

// リンク集
Route::post('/slack/links', [SlackController::class, 'links']);