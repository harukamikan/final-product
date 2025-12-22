@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-xl">
        <div class="rounded-3xl border bg-white shadow-sm p-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">会社を作成</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        会社を作成すると、招待コードを発行できます。メンバーはそのコードで参加します。
                    </p>
                </div>
                <div class="text-3xl">🏢</div>
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>・{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('company.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="text-sm font-medium text-gray-700">会社名</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="例）Fusic / ○○開発部"
                        class="mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">招待コード（任意）</label>
                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="例）fusic-2025（半角英小文字・数字・ハイフン）"
                        class="mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        未入力の場合は会社名から自動生成します（日本語の場合はランダムになります）。
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-2xl bg-indigo-600 text-white py-3 font-semibold hover:bg-indigo-700 transition"
                >
                    会社を作成して招待コードを発行
                </button>

                <div class="text-xs text-gray-500 text-center pt-1">
                    すでに招待コードを持っている場合は
                    <a href="{{ route('company.join') }}" class="text-indigo-600 hover:underline">会社に参加</a>
                    へ
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
