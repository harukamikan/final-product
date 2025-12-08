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
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Slack認証に失敗しました。');
        }

        $user = User::where('slack_id', $slackUser->id)->first();

        if (!$user && !empty($slackUser->email)) {
            $user = User::where('email', $slackUser->email)->first();
        }

        $email = $slackUser->email ?? ($slackUser->id . '@slack.local');

        if (!$user) {
            $user = User::create([
                'name'      => $slackUser->name ?? $slackUser->nickname ?? 'Slack User',
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
