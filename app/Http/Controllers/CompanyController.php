<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

        // 既に所属済みならダッシュボードへ
        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        return view('company.create');
    }

    /**
     * 会社を作成して、作成者を所属させる（招待コード = slug）
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->company_id) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 招待コードをユーザーに決めさせてもOK。空なら自動生成。
            'slug' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/'],
        ], [
            'slug.regex' => '招待コードは半角英小文字・数字・ハイフンのみで入力してください。',
        ]);

        $name = trim($data['name']);
        $slug = isset($data['slug']) && $data['slug'] !== ''
            ? strtolower(trim($data['slug']))
            : Str::slug($name);

        // slugが空になるケース（日本語名など）の保険
        if ($slug === '') {
            $slug = 'company-' . Str::lower(Str::random(8));
        }

        // 重複したら末尾にランダムを付けて回避（手入力slugの場合はエラーにしたいなら分岐）
        if (Company::where('slug', $slug)->exists()) {
            // 手入力で slug を入れてたならエラーにする方が親切
            if (!empty($data['slug'])) {
                return back()->withInput()->withErrors([
                    'slug' => 'この招待コードは既に使われています。別のコードを入力してください。',
                ]);
            }

            // 自動生成なら衝突回避
            $slug = $slug . '-' . Str::lower(Str::random(4));
        }

        $company = Company::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        // 作成者を所属させる
        $user->company_id = $company->id;
        $user->save();

        return redirect()
            ->route('dashboard')
            ->with('success', "会社「{$company->name}」を作成しました！招待コード：{$company->slug}");
    }

    /**
     * 会社参加（招待コード入力）画面
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
     * 招待コード（slug）で会社に参加
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
