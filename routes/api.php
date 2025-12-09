<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Activity;
use App\Models\User;

Route::post('/slack/test', function (Request $request) {
    \Log::info('Slack command received:', $request->all());
    
    return response()->json([
        'text' => 'Hello from Laravel! 🎉'
    ]);
});

Route::post('/slack/activity', function (Request $request) {
    // Slackから送られてきたユーザーIDを取得
    $slackUserId = $request->input('user_id');
    
    // SlackユーザーIDでLaravelユーザーを検索
    $user = User::where('slack_id', $slackUserId)->first();
    
    if (!$user) {
        return response()->json([
            'text' => '❌ ユーザーが見つかりません。先にWebページでSlackログインしてください: https://final-product-production.up.railway.app/login'
        ]);
    }
    
    // Slackから送られてきたデータを取得
    $text = $request->input('text'); // 例: "ブログ https://example.com"
    
    // スペースで分割
    $parts = explode(' ', $text, 2);
    $type = $parts[0] ?? '';  // 種別（ブログ、資格など）
    $url = $parts[1] ?? '';   // URL
    
    // データベースに保存
    $activity = Activity::create([
        'user_id' => $user->id,
        'type' => $type,
        'url' => $url,
    ]);
    
    // ログに記録
    \Log::info('Activity received:', [
        'type' => $type,
        'url' => $url,
        'slack_user_id' => $slackUserId,
        'user_id' => $user->id
    ]);
    
    return response()->json([
        'text' => "✅ 活動を登録しました！\n種別: {$type}\nURL: {$url}"
    ]);
});

Route::post('/slack/list', function (Request $request) {
    // Slackから送られてきたユーザーIDを取得
    $slackUserId = $request->input('user_id');
    
    // SlackユーザーIDでLaravelユーザーを検索
    $user = User::where('slack_id', $slackUserId)->first();
    
    if (!$user) {
        return response()->json([
            'text' => '❌ ユーザーが見つかりません。先にWebページでSlackログインしてください: https://final-product-production.up.railway.app/login'
        ]);
    }
    
    $activities = Activity::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    if ($activities->isEmpty()) {
        return response()->json([
            'text' => "📝 登録されている活動はまだありません。\n`/activity [種別] [URL]` で登録できます！"
        ]);
    }
    
    $text = "📋 *あなたの最近の活動（最新10件）*\n\n";
    foreach ($activities as $activity) {
        $date = $activity->created_at->format('Y/m/d');
        $text .= "• [{$activity->type}] {$activity->url}\n";
        $text .= "  登録日: {$date}\n\n";
    }
    
    return response()->json([
        'response_type' => 'in_channel',
        'text' => $text
    ]);
});