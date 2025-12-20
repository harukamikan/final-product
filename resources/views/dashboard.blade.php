@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ===== ページタイトル ===== --}}
        <h2 class="text-2xl font-bold text-slate-800">
            ようこそ、{{ auth()->user()->name }}さん
        </h2>

        {{-- ===== 半期目標 ===== --}}
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    📋 今期の半期目標
                </h3>

                @if($semesterGoal)
                <a
                    href="{{ route('semester-goals.edit', $semesterGoal) }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    編集
                </a>
                @else
                <a
                    href="{{ route('semester-goals.create') }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    設定する
                </a>
                @endif
            </div>

            @if($semesterGoal)
            <div class="border-l-4 border-indigo-500 bg-indigo-50 p-4 rounded-r-lg">
                <div class="text-sm font-semibold text-indigo-700 mb-1">
                    {{ $semesterGoal->category }}
                </div>
                <div class="text-lg text-slate-800 font-medium">
                    {{ $semesterGoal->title }}
                </div>
                @if($semesterGoal->deadline)
                <div class="text-sm text-slate-500 mt-2">
                    期限：{{ $semesterGoal->deadline }}
                </div>
                @endif
            </div>
            @else
            <div class="border-l-4 border-slate-300 bg-slate-50 p-4 rounded-r-lg text-slate-500">
                半期目標が設定されていません
            </div>
            @endif
        </div>


        {{-- ===== マイル統計 ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- 総マイル --}}
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 uppercase mb-2">
                    総マイル
                </p>
                <p class="text-4xl font-bold text-indigo-600 mb-1">
                    {{ $totalMiles }}
                </p>
                <p class="text-sm text-slate-500">
                    累計獲得マイル
                </p>
            </div>

            {{-- ランク --}}
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 uppercase mb-2">
                    現在のランク
                </p>

                <div class="flex justify-center items-center gap-2 text-2xl font-bold mb-3
                    @if($rank === 'ゴールド') text-yellow-400
                    @elseif($rank === 'シルバー') text-slate-400
                    @else text-orange-500
                    @endif">
                    <span>
                        @if($rank === 'ゴールド') 🥇
                        @elseif($rank === 'シルバー') 🥈
                        @else 🥉
                        @endif
                    </span>
                    <span>{{ $rank }}</span>
                </div>

                @php
                $nextRank = $rank === 'ブロンズ' ? 'シルバー' : ($rank === 'シルバー' ? 'ゴールド' : null);
                $nextMiles = $rank === 'ブロンズ' ? 200 : ($rank === 'シルバー' ? 500 : null);
                $remaining = $nextMiles ? max(0, $nextMiles - $totalMiles) : 0;
                $progress = $nextMiles ? min(100, ($totalMiles / $nextMiles) * 100) : 100;
                @endphp

                @if($nextRank)
                <div class="mt-4">
                    <div class="w-full bg-slate-200 rounded-full h-2 mb-2">
                        <div
                            class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                            style="width: {{ $progress }}%">
                        </div>
                    </div>
                    <p class="text-sm text-slate-600">
                        {{ $nextRank }}まであと {{ $remaining }} マイル
                    </p>

                    @if($recommendedMission)
                    <div class="mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                        <p class="text-sm font-semibold text-yellow-700">
                            💡 {{ $recommendedMission->title }}
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">
                            +{{ $recommendedMission->reward_miles }} マイル獲得
                        </p>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-slate-500 mt-3">
                    最高ランク達成 🎉
                </p>
                @endif
            </div>

            {{-- 今月の活動 --}}
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 uppercase mb-2">
                    今月の活動
                </p>
                <p class="text-4xl font-bold text-indigo-600 mb-1">
                    {{ $thisMonthGoals }}
                </p>
                <p class="text-sm text-slate-500">
                    件
                </p>
            </div>
        </div>

        {{-- ===== 新規作成ボタン ===== --}}
        <div>
            <a
                href="{{ route('goals.create') }}"
                class="inline-flex items-center px-4 py-2 rounded-lg
                       border border-indigo-600 text-indigo-600
                       font-semibold text-sm
                       hover:bg-indigo-600 hover:text-white transition">
                ➕ 新しい活動を記録
            </a>
        </div>

        {{-- ===== 最近の活動 ===== --}}
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">
                📝 最近の記録
            </h3>

            @if($recentGoals->count())
            <div class="space-y-4">
                @foreach($recentGoals as $goal)
                <div class="border border-slate-200 rounded-xl p-5 hover:shadow-md transition">
                    <div class="flex justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-indigo-600">
                                {{ $goal->category }}
                            </p>
                            <p class="text-slate-800 font-medium mt-1">
                                {{ $goal->title }}
                            </p>
                            @if($goal->deadline)
                            <p class="text-sm text-slate-500 mt-1">
                                期限：{{ $goal->deadline }}
                            </p>
                            @endif
                        </div>
                        <a
                            href="{{ route('goals.edit', $goal) }}"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            編集
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a
                    href="{{ route('activities.index') }}"
                    class="inline-block px-4 py-2 rounded-lg
                               border border-indigo-600 text-indigo-600
                               font-semibold text-sm
                               hover:bg-indigo-600 hover:text-white transition">
                    すべての活動を見る
                </a>
            </div>
            @else
            <div class="text-center py-10 text-slate-500">
                <p class="mb-4">まだ目標が登録されていません</p>
                <a
                    href="{{ route('goals.create') }}"
                    class="font-semibold text-indigo-600 hover:text-indigo-800">
                    最初の目標を作成
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection