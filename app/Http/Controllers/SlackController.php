<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SlackController extends Controller
{
    
    public function redirect()
    {
        return Socialite::driver('slack')->redirect();
    }

    
    public function callback()
    {
        try {
            $slackUser = Socialite::driver('slack')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'slack' => 'Slackログインに失敗しました。',
            ]);
        }

        $email    = $slackUser->getEmail();
        $name     = $slackUser->getName() ?? 'Slack User';
        $slackId  = $slackUser->getId();
        $avatar   = $slackUser->getAvatar();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'slack_id' => $slackId,
                'password' => bcrypt(Str::random(32)), 
            ]);
        } else {
            if (!$user->slack_id) {
                $user->update(['slack_id' => $slackId]);
            }
        }

        Auth::login($user);

        return redirect('/dashboard');
    }
}
