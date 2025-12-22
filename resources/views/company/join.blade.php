@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-lg">
        <div class="rounded-3xl border bg-white shadow-sm p-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">会社に参加</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        管理者から共有された招待コードを入力してください。
                    </p>
                </div>
                <div class="text-3xl">🏢</div>
            </div>

            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>・{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('company.join.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium text-gray-700">招待コード</label>
                    <input
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="例）demo / fusic-2025"
                        class="mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        ※ 招待コードは会社ごとに発行されます（companies.slug）。
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-2xl bg-indigo-600 text-white py-3 font-semibold hover:bg-indigo-700 transition"
                >
                    参加する
                </button>

                <div class="text-xs text-gray-500 text-center pt-2">
                    招待コードが分からない場合は、管理者に確認してください。
                </div>
                <div class="text-xs text-gray-500 text-center pt-2">
                    招待コードを持っていない場合は
                    <a href="{{ route('company.create') }}" class="text-indigo-600 hover:underline">会社を作成</a>
                    できます。
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
