<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

class SlackAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('slack')->stateless()->redirect();
    }

   public function callback()
{
    try {
        $slackUser = Socialite::driver('slack')->stateless()->user();
        
        // デバッグ: Slackから取得した情報を確認
        dd([
            'token' => $slackUser->token,
            'id' => $slackUser->id,
            'name' => $slackUser->name,
            'nickname' => $slackUser->nickname,
            'email' => $slackUser->email,
            'user_array' => $slackUser->user, // この中身が重要
            'getRaw' => $slackUser->getRaw(), // 生データ全部
        ]);
        
    } catch (\Exception $e) {
        return redirect('/login')->with('error', 'Slack認証に失敗しました。');
    }

    $user = User::where('slack_id', $slackUser->id)->first();

    if (!$user && !empty($slackUser->email)) {
        $user = User::where('email', $slackUser->email)->first();
    }

    $email = $slackUser->email ?? ($slackUser->id . '@slack.local');
    
    // 名前の取得を改善
    $name = $slackUser->user['real_name'] 
         ?? $slackUser->user['profile']['real_name']
         ?? $slackUser->name 
         ?? $slackUser->nickname 
         ?? 'Slack User';

    if (!$user) {
        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'slack_id'  => $slackUser->id,
            'password'  => bcrypt(Str::random(16)),
        ]);
    } else {
        if (!$user->slack_id) {
            $user->slack_id = $slackUser->id;
            $user->save();
        }
    }

    Auth::login($user, true);

    return redirect('/dashboard');
}
}
