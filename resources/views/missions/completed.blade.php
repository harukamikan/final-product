@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- ----- マイル残高カード ----- --}}
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

    {{-- ----- タブ切り替え ----- --}}
   <div class="flex gap-4 text-sm font-medium">
    <a href="{{ route('missions.index') }}"
       class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.index') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        進行中のミッション
    </a>

    <a href="{{ route('missions.completed') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.completed') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        完了したミッション
    </a>

    <a href="{{ route('missions.personal') }}"
        class="px-4 py-2 rounded-xl {{ request()->routeIs('missions.personal') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        個人ミッション
    </a>
    </div>
    {{-- ----- ミッションがないとき ----- --}}
    @if ($missions->isEmpty())
        <div class="text-center p-10 bg-white rounded-3xl shadow-sm">
            <div class="text-5xl mb-4">🎉</div>
            <p class="text-xl font-semibold">まだ完了したミッションがありません</p>
            <p class="text-gray-500 mt-2">進行中のミッションを達成していきましょう！</p>
        </div>
    @endif

    {{-- ----- 完了ミッションカード一覧 ----- --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($missions as $mission)
            <div class="rounded-3xl border bg-white px-5 py-6 shadow-sm space-y-4">

                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $mission->title }}</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $mission->description }}
                        </p>
                    </div>

                    {{-- 報酬マイル --}}
                    <div class="text-right">
                        <p class="text-xs text-gray-500">獲得マイル</p>
                        <p class="text-lg font-bold text-emerald-600">
                            +{{ $mission->reward_miles }} mile
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-sm font-medium">
                        🎉 達成済み
                    </span>
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
