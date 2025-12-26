<?php

namespace App\Http\Controllers;

use App\Models\InviteToken;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    // 招待リンクを踏んだ
    public function accept(Request $request, string $token)
    {
        $invite = InviteToken::where('token', $token)->firstOrFail();

        abort_if(!$invite->canUse(), 410, '招待リンクが無効または期限切れです。');

        // 登録後に company_id を付けるためセッションに保存
        session([
            'invite_company_id' => $invite->company_id,
            'invite_token_id'   => $invite->id,
        ]);

        // 未ログインなら登録へ（ログイン済みなら join させる設計も可能）
        if (!$request->user()) {
            return redirect()->route('register')
                ->with('info', '招待リンクを受け取りました。登録すると自動で会社に参加します。');
        }

        // すでにログインしてる人（必要なら即所属させてもOK）
        return redirect()->route('dashboard');
    }
}
