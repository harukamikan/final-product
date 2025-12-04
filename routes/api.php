<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/slack/test', function (Request $request) {
    \Log::info('Slack command received:', $request->all());
    
    return response()->json([
        'text' => 'Hello from Laravel! 🎉'
    ]);
});
Route::post('/slack/activity', function (Request $request) {
    // Slackから送られてきたデータを取得
    $text = $request->input('text'); // 例: "ブログ https://example.com"
    
    // スペースで分割
    $parts = explode(' ', $text, 2);
    $type = $parts[0] ?? '';  // 種別（ブログ、資格など）
    $url = $parts[1] ?? '';   // URL
    
    // ログに記録（後でDBに保存する）
    \Log::info('Activity received:', [
        'type' => $type,
        'url' => $url,
        'user' => $request->input('user_name')
    ]);
    
    return response()->json([
        'text' => "✅ 活動を登録しました！\n種別: {$type}\nURL: {$url}"
    ]);
});