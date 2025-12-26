<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\InviteToken;
use Illuminate\Support\Str;

class SlackAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('slack')->stateless()->redirect();
    }

    public function callback(Request $request)
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
        $name  = $slackUser->name ?? 'Slack User';

        if (!$user) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'slack_id' => $slackUser->id,
                'password' => bcrypt(Str::random(16)),
            ]);
        } else {
            $user->name = $name;
            $user->slack_id = $slackUser->id;
            $user->save();
        }

        // ✅ 招待リンク経由なら company_id を自動付与（Slackログインでも）
        $inviteCompanyId = $request->session()->get('invite_company_id');
        $inviteTokenId   = $request->session()->get('invite_token_id');

        if ($inviteCompanyId && empty($user->company_id)) {
            $user->company_id = $inviteCompanyId;
            $user->save();

            if ($inviteTokenId) {
                InviteToken::where('id', $inviteTokenId)->increment('used_count');
            }

            $request->session()->forget(['invite_company_id', 'invite_token_id']);
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
