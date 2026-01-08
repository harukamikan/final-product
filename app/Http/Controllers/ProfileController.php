<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\InviteToken;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $inviteLink = null;

        // ユーザーが会社に所属している場合、招待リンクを取得
        if ($user->company_id) {
            $invite = InviteToken::where('company_id', $user->company_id)
                ->where('max_uses', 0)
                ->whereNull('expires_at')
                ->first();

            if ($invite) {
                $inviteLink = route('invite.accept', ['token' => $invite->token]);
            }
        }

        return view('profile.edit', [
            'user' => $user,
            'inviteLink' => $inviteLink,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($request->filled('background_type') && $request->filled('background_value')) {
            $user->background_type  = $request->background_type;
            $user->background_value = $request->background_value;
        }

        // リマインド通知のON/OFF
        $user->reminder_enabled = $request->has('reminder_enabled');

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'profile-updated');
    }



    /**
     * Delete the user's account.
     */
    public function confirmDelete()
    {
        return view('profile.confirm-delete');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('status', 'account-deleted');
    }

    /**
     * 招待リンクを再生成する
     */
    public function regenerateInviteLink(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 会社に所属していない場合はエラー
        if (!$user->company_id) {
            return redirect()
                ->route('profile.edit')
                ->withErrors(['invite' => '会社に所属していないため、招待リンクを生成できません。']);
        }

        // レート制限: 5分以内の再生成を防止
        $cacheKey = "invite_regenerate_{$user->id}";
        if (Cache::has($cacheKey)) {
            return redirect()
                ->route('profile.edit')
                ->withErrors(['invite' => '招待リンクの再生成は5分間に1回のみ可能です。しばらく待ってから再度お試しください。']);
        }

        // 既存の招待トークンを取得または新規作成
        $invite = InviteToken::where('company_id', $user->company_id)
            ->where('max_uses', 0)
            ->whereNull('expires_at')
            ->first();

        if ($invite) {
            // 既存のトークンを更新（トークン文字列は変更しない）
            $invite->touch(); // updated_at を更新
        } else {
            // 新規作成
            $invite = InviteToken::create([
                'company_id' => $user->company_id,
                'token' => Str::random(60),
                'expires_at' => null,
                'max_uses' => 0,
                'used_count' => 0,
            ]);
        }

        // レート制限を設定（5分間）
        Cache::put($cacheKey, true, now()->addMinutes(5));

        return redirect()
            ->route('profile.edit')
            ->with('invite_regenerated', '招待リンクを更新しました。');
    }
}
