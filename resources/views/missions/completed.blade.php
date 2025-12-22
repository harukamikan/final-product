@extends('layouts.app')

@section('content')
<div
    x-data="{ open: false }"
    class="relative max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- ================= 右上：検索トグルボタン ================= --}}
    <button
        @click="open = !open"
        class="fixed top-24 right-6 z-30
               bg-white/70 backdrop-blur
               shadow-lg rounded-full p-3
               hover:bg-white transition"
        title="検索">
        🔍
    </button>

    {{-- ================= 検索バー ================= --}}
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="fixed top-36 right-6 z-20
           w-80 max-w-[calc(100vw-2rem)]
           bg-white/90 backdrop-blur
           rounded-2xl shadow-xl p-5 space-y-4">
        マイル履歴検索
        </h3>

        <form method="GET" action="{{ route('missions.completed') }}" class="space-y-3">

            {{-- ミッション名検索 --}}
            <div>
                <label class="text-xs text-gray-500">ミッション名</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="例：イベントを企画・開催する"
                    class="w-full mt-1 px-3 py-2 text-sm
                           rounded-lg border border-gray-300
                           focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- 獲得日（期間）検索 --}}
            <div>
                <label class="text-xs text-gray-500">獲得期間</label>

                <div class="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center">
                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="w-full min-w-0 px-3 py-2 text-sm rounded-lg border border-gray-300">

                    <span class="hidden sm:flex items-center text-xs text-gray-400 justify-center">
                        〜
                    </span>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="w-full min-w-0 px-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
            </div>


            <div class="flex justify-between pt-2">
                <a
                    href="{{ route('missions.completed') }}"
                    class="text-xs text-gray-500 hover:underline">
                    リセット
                </a>

                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg
                           bg-indigo-600 text-white text-sm
                           hover:bg-indigo-700">
                    検索
                </button>
            </div>
        </form>
    </div>

    {{-- ================= マイル残高カード ================= --}}
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-3xl p-6 shadow-md flex justify-between items-center">
        <div>
            <p class="text-sm opacity-90">現在のマイル残高</p>
            <p class="text-4xl font-bold">
                {{ $totalMiles }}
                <span class="text-xl opacity-70 ml-1">mile</span>
            </p>
        </div>
        <div class="text-5xl">🏆</div>
    </div>

<<<<<<< HEAD
    {{-- ----- タブ切り替え ----- --}}
   <div class="flex gap-4 text-sm font-medium">
    <a href="{{ route('missions.index') }}"
       class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.index') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        進行中のミッション
    </a>
=======
    {{-- ================= タブ切り替え ================= --}}
    <div class="flex gap-4 text-sm font-medium">
        <a href="{{ route('missions.index') }}"
            class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600">
            進行中のミッション
        </a>
>>>>>>> develop

    <a href="{{ route('missions.completed') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.completed') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        完了したミッション
    </a>

    <a href="{{ route('missions.personal') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.personal') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        個人ミッション
    </a>
    </div>
<<<<<<< HEAD
    {{-- ----- ミッションがないとき ----- --}}
    @if ($missions->isEmpty())
        <div class="text-center p-10 bg-white rounded-3xl shadow-sm">
            <div class="text-5xl mb-4">🎉</div>
            <p class="text-xl font-semibold">まだ完了したミッションがありません</p>
            <p class="text-gray-500 mt-2">進行中のミッションを達成していきましょう！</p>
        </div>
=======

    {{-- ================= ミッションなし ================= --}}
    @if ($mileHistories->isEmpty())
    <div class="text-center p-10 bg-white rounded-3xl shadow-sm">
        <div class="text-5xl mb-4">🎉</div>
        <p class="text-xl font-semibold">まだ完了したミッションがありません</p>
        <p class="text-gray-500 mt-2">進行中のミッションを達成していきましょう！</p>
    </div>
>>>>>>> develop
    @endif

    {{-- ================= 完了ミッション一覧 ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($mileHistories as $history)
        <div class="rounded-3xl border bg-white px-5 py-6 shadow-sm space-y-4">

            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $history->mission->title }}
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        {{ $history->mission->description }}
                    </p>

                    <p class="text-xs text-gray-400 mt-2">
                        🗓 獲得日：
                        {{ $history->created_at->format('Y年m月d日') }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-500">獲得マイル</p>
                    <p class="text-lg font-bold text-emerald-600">
                        +{{ $history->miles }} mile
                    </p>
                </div>
            </div>

            <div class="pt-3 border-t">
                <span class="inline-flex items-center px-3 py-1 rounded-full
                         bg-emerald-50 text-emerald-700
                         text-sm font-medium">
                    🎉 達成済み
                </span>
            </div>

        </div>
        @endforeach

    </div>

</div>
@endsection