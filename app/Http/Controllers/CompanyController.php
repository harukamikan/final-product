<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\InviteToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * 会社作成画面
     */
    public function showCreateForm(Request $request)
    {
        $user = $request->user();

        /**
         * ✅ 既に所属済みでも、作成直後だけ「招待リンク表示」のために create 画面を見せたい
         * そのため session('invite_link') がある場合は例外的に表示を許可する
         */
        if ($user->company_id && !$request->session()->has('invite_link')) {
            return redirect()->route('dashboard');
        }

        return view('company.create');
    }

    /**
     * 会社を作成して、作成者を所属させる
     * さらに「招待リンク（invite token）」を発行して company/create に表示する
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // slug は「会社の識別子」として残してOK（招待は token にする）
            'slug' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/'],
        ], [
            'slug.regex' => '会社slugは半角英小文字・数字・ハイフンのみで入力してください。',
        ]);

        $name = trim($data['name']);
        $slug = isset($data['slug']) && $data['slug'] !== ''
            ? strtolower(trim($data['slug']))
            : Str::slug($name);

        // slugが空になるケース（日本語名など）の保険
        if ($slug === '') {
            $slug = 'company-' . Str::lower(Str::random(8));
        }

        // slug 重複チェック
        if (Company::where('slug', $slug)->exists()) {
            if (!empty($data['slug'])) {
                return back()->withInput()->withErrors([
                    'slug' => 'この会社slugは既に使われています。別のslugを入力してください。',
                ]);
            }
            $slug = $slug . '-' . Str::lower(Str::random(4));
        }

        // 会社作成
        $company = Company::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        // 作成者を所属させる
        $user->company_id = $company->id;
        $user->save();

        /**
         * ✅ 会社ごとの「常設招待リンク」を1本にする
         * 既に存在すればそれを再利用、なければ新規作成
         */
        $invite = InviteToken::where('company_id', $company->id)
            ->where('max_uses', 0)
            ->whereNull('expires_at')
            ->first();

        if (!$invite) {
            $invite = InviteToken::create([
                'company_id' => $company->id,
                'token'      => Str::random(60),
                'expires_at' => null,
                'max_uses'   => 0,
                'used_count' => 0,
            ]);
        }

        $inviteLink = route('invite.accept', ['token' => $invite->token]);

        /**
         * ✅ 作成直後は company/create に戻して、招待リンクコピーUIを出す
         */
        return redirect()
            ->route('company.create')
            ->with([
                'success'     => "会社「{$company->name}」を作成しました！このリンクをコピーしてメンバーを招待できます。",
                'invite_link' => $inviteLink,
            ]);
    }

    /**
     * 会社参加（招待コード入力）画面（保険）
     */
    public function showJoinForm(Request $request)
    {
        $user = $request->user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        return view('company.join');
    }

    /**
     * 招待コード（slug）で会社に参加（保険）
     */
    public function join(Request $request)
    {
        $user = $request->user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = strtolower(trim($data['code']));

        $company = Company::where('slug', $code)->first();

        if (!$company) {
            return back()
                ->withInput()
                ->withErrors(['code' => '招待コードが見つかりませんでした。もう一度確認してください。']);
        }

        $user->company_id = $company->id;
        $user->save();

        return redirect()
            ->route('dashboard')
            ->with('success', "「{$company->name}」に参加しました！");
    }
}
