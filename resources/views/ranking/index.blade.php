@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 px-6">

    <h1 class="text-2xl font-bold text-center mb-8">
        ランキング
    </h1>

    <div x-data="{ tab: 'mile' }">

        {{-- 切り替えボタン --}}
        <div class="flex justify-center gap-4 mb-6">
            <button
                @click="tab = 'mile'"
                :class="tab === 'mile'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700'"
                class="px-4 py-2 rounded-md font-semibold"
            >
                マイル
            </button>

            <button
                @click="tab = 'mission'"
                :class="tab === 'mission'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700'"
                class="px-4 py-2 rounded-md font-semibold"
            >
                達成ミッション数
            </button>
        </div>

        {{-- 自分の順位（固定表示） --}}
        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-300
                    rounded-lg shadow text-center
                    sticky top-4 z-10">

            <template x-if="tab === 'mile'">
                <p class="font-semibold">
                    あなたの順位：
                    {{ $myMileRank }} 位 / {{ $mileUserCount }} 人中<br>
                    <span class="text-gray-600">
                        累計 {{ auth()->user()->total_miles }} mile
                    </span>
                </p>
            </template>

            <template x-if="tab === 'mission'">
                <p class="font-semibold">
                    あなたの順位：
                    {{ $myMissionRank }} 位 / {{ $missionUserCount }} 人中<br>
                    <span class="text-gray-600">
                        累計 {{ auth()->user()->completed_missions }} 件
                    </span>
                </p>
            </template>

        </div>

        {{-- マイルランキング --}}
        <div x-show="tab === 'mile'" class="space-y-3">
            @foreach ($mileRankers as $index => $user)
                @php
                    $rank = $index + 1;

                    $rankClass = match ($rank) {
                        1 => 'bg-yellow-100 border-yellow-400',
                        2 => 'bg-gray-100 border-gray-400',
                        3 => 'bg-orange-100 border-orange-400',
                        default => 'bg-white border-gray-200',
                    };
                @endphp

                <div class="flex justify-between items-center
                            p-4 rounded-lg border shadow-sm {{ $rankClass }}">
                    <div class="font-semibold">
                        {{ $rank }} 位　{{ $user->nickname ?? $user->name }}
                    </div>
                    <div class="font-bold">
                        {{ $user->total_miles }} mile
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ミッション数ランキング --}}
        <div x-show="tab === 'mission'" class="space-y-3">
            @foreach ($missionRankers as $index => $user)
                @php
                    $rank = $index + 1;

                    $rankClass = match ($rank) {
                        1 => 'bg-yellow-100 border-yellow-400',
                        2 => 'bg-gray-100 border-gray-400',
                        3 => 'bg-orange-100 border-orange-400',
                        default => 'bg-white border-gray-200',
                    };
                @endphp

                <div class="flex justify-between items-center
                            p-4 rounded-lg border shadow-sm {{ $rankClass }}">
                    <div class="font-semibold">
                        {{ $rank }} 位　{{ $user->nickname ?? $user->name }}
                    </div>
                    <div class="font-bold">
                        {{ $user->completed_missions }} 件
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
