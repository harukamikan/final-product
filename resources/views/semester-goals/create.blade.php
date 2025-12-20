{{-- resources/views/semester-goals/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 space-y-8">

        {{-- ===== タイトル ===== --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                半期目標を設定
            </h1>
            <p class="text-sm text-slate-700 mt-1">
                今期（半期）のメイン目標を設定しましょう
            </p>
        </div>

        {{-- ===== フォーム ===== --}}
        <form
            method="POST"
            action="{{ route('semester-goals.store') }}"
            class="bg-white rounded-2xl shadow-md p-6 space-y-6"
        >
            @csrf

            {{-- カテゴリ --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    カテゴリ
                </label>
                <select
                    name="category"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="">選択してください</option>
                    <option value="学業">学業</option>
                    <option value="仕事">仕事</option>
                    <option value="スキル">スキル</option>
                    <option value="健康">健康</option>
                    <option value="その他">その他</option>
                </select>
            </div>

            {{-- タイトル --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    半期目標
                </label>
                <input
                    type="text"
                    name="title"
                    placeholder="例：基本情報技術者試験に合格する"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
            </div>

            {{-- 詳細 --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    目標の詳細（任意）
                </label>
                <textarea
                    name="description"
                    rows="4"
                    placeholder="達成条件や意識することなど"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
            </div>

            {{-- 期限 --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    期限（任意）
                </label>
                <input
                    type="date"
                    name="deadline"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- 今期フラグ --}}
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_current"
                    value="1"
                    checked
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                >
                <span class="text-sm text-slate-600">
                    今期の半期目標として設定する
                </span>
            </div>

            {{-- ボタン --}}
            <div class="flex justify-end gap-3 pt-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="px-4 py-2 rounded-lg border border-slate-300
                           text-slate-600 font-semibold text-sm
                           hover:bg-slate-100 transition">
                    キャンセル
                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg
                           bg-indigo-600 text-white
                           font-semibold text-sm
                           hover:bg-indigo-700 transition">
                    保存する
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
