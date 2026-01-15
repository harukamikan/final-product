@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ===== ページタイトル ===== --}}
        <h2 class="text-2xl font-bold text-slate-800">
            ようこそ、{{ auth()->user()->name }}さん
        </h2>

        {{-- ===== 所属会社 ===== --}}
        @if(auth()->user()->company)
        <p class="text-sm mt-1">
            <span class="font-semibold text-gray-900">所属会社：</span><span class="font-semibold text-indigo-600">{{ auth()->user()->company->name }}</span>
        </p>
        @endif

        {{-- ===== オンボーディング完了ウェルカムメッセージ ===== --}}
        @if(session('onboarding_completed'))
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl shadow-lg p-6 mt-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-3xl">🎉</span>
                        <h3 class="text-xl font-bold">ようこそ！</h3>
                    </div>
                    <p class="text-base leading-relaxed opacity-95">
                        {{ session('onboarding_completed') }}
                    </p>
                </div>
                <a
                    href="{{ route('missions.index') }}"
                    class="shrink-0 inline-flex items-center gap-2
                           px-6 py-3 rounded-xl
                           bg-white text-indigo-600
                           text-sm font-bold
                           hover:bg-indigo-50 active:scale-95
                           transition-all duration-200 shadow-md">
                    <span>ミッション画面に行く</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

        {{-- ===== 報酬アンケート通知 ===== --}}
        @if($showRewardSurveyNotice && $pendingSurvey)
        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-base font-semibold text-indigo-700">
                        🎁 報酬アンケート回答依頼が来ています
                    </p>

                    <p class="text-sm text-indigo-600 mt-1">
                        希望する報酬を第1〜第3希望まで入力してください。
                        @if($pendingSurvey->end_at)
                        <span class="font-semibold text-indigo-700">
                            （{{ $pendingSurvey->end_at->format('Y年m月d日') }} まで）
                        </span>
                        @endif
                    </p>
                </div>

                <a
                    href="{{ route('reward-survey.create', $pendingSurvey) }}"
                    class="shrink-0 inline-flex items-center
                   px-4 py-2 rounded-lg
                   bg-indigo-600 text-white
                   text-sm font-semibold
                   hover:bg-indigo-700 transition">
                    回答する
                </a>
            </div>
        </div>
        @endif

        {{-- ⏰ 7日以内に期限が切れる報酬 --}}
        @if($expiringRewards->isNotEmpty())
        <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-5 mb-6">
            <h3 class="font-bold text-yellow-800 mb-2">
                ⏰ まもなく期限が切れる報酬
            </h3>

            <ul class="space-y-1 text-sm text-yellow-900">
                @foreach($expiringRewards as $ur)
                <li>
                    🎁 {{ $ur->reward->name }}
                    <span class="text-xs text-yellow-700">
                        （{{ $ur->expires_at->diffForHumans() }}）
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ===== 半期目標 ===== --}}
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    📋 今期の半期目標
                </h3>

                @if($semesterGoal)
                <a href="{{ route('semester-goals.edit', $semesterGoal) }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    編集
                </a>
                @else
                <a href="{{ route('semester-goals.create') }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    設定する
                </a>
                @endif
            </div>

            @if($semesterGoal)
            @php
            $daysLeft = $semesterGoal->deadline
            ? now()->startOfDay()->diffInDays(
            \Carbon\Carbon::parse($semesterGoal->deadline)->startOfDay(),
            false
            )
            : null;

            $isUrgent = $daysLeft !== null && $daysLeft <= 30;
                @endphp


                <div class="border-l-4 p-4 rounded-r-lg
                    {{ $isUrgent ? 'border-red-500 bg-red-50' : 'border-indigo-500 bg-indigo-50' }}">

                <div class="text-sm font-semibold mb-1
                        {{ $isUrgent ? 'text-red-700' : 'text-indigo-700' }}">
                    {{ $semesterGoal->category }}
                </div>

                <div class="text-lg font-medium text-slate-800">
                    {{ $semesterGoal->title }}
                </div>

                @if($semesterGoal->deadline)
                <div class="text-sm text-slate-500 mt-2">
                    期限：{{ $semesterGoal->deadline->format('Y-m-d') }}
                </div>

                <div class="mt-2 text-sm font-semibold
                            {{ $isUrgent ? 'text-red-600' : 'text-slate-600' }}">
                    ⏳ 残り {{ $daysLeft }} 日
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- 総マイル --}}
        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col items-center justify-center min-h-[250px]">
            <p class="text-base font-semibold text-slate-500 uppercase mb-3">
                総マイル
            </p>
            <p class="text-5xl font-bold text-indigo-600 mb-2">
                {{ $totalMiles }}
            </p>
            <p class="text-base text-slate-500">
                今期獲得マイル
            </p>
        </div>

        {{-- ランク --}}
        <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center justify-center">
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
            <div class="mt-4 w-full">
                <div class="w-full max-w-md bg-slate-200 rounded-full h-3 mb-3">
                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300"
                        style="width: {{ $progress }}%">
                    </div>
                </div>
                <p class="text-base text-slate-600 font-medium text-center">
                    {{ $nextRank }}まであと {{ $remaining }} マイル
                </p>

                {{-- ★ クリック可能なおすすめミッション --}}
                @if($recommendedMission)
                <a href="{{ route('missions.show', $recommendedMission) }}"
                    class="block mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-400
                                      rounded-r-lg text-left
                                      hover:bg-yellow-100 transition cursor-pointer">
                    <p class="text-sm font-semibold text-yellow-700">
                        💡 {{ $recommendedMission->title }}
                    </p>
                    <p class="text-xs text-yellow-600 mt-1">
                        +{{ $recommendedMission->reward_miles }} マイル獲得
                    </p>
                </a>
                @endif
            </div>
            @else
            <p class="text-sm text-slate-500 mt-3">
                最高ランク達成 🎉
            </p>
            @endif
        </div>

        {{-- 週間活動グラフ --}}
        <div class="bg-white rounded-2xl shadow-md p-6">
            <p class="text-sm font-semibold text-slate-500 uppercase mb-4">
                📊 週間活動
            </p>
            <div style="height: 200px;">
                <canvas id="weeklyActivityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== 最近の活動 ===== --}}
    <div
        class="bg-white rounded-2xl shadow-md p-6 relative"
        x-data="{ open: false }">

        {{-- ツールチップ --}}
        <div
            x-show="open"
            x-transition.opacity.scale.95
            @click.outside="open = false"
            class="absolute bottom-full right-4 mb-3
                   px-4 py-2
                   text-xs text-slate-700
                   bg-indigo-50
                   rounded-xl
                   shadow-lg
                   border border-slate-200
                   whitespace-nowrap
                   flex items-center gap-1">
            日々の活動を記録しましょう
            {{-- 吹き出しの三角 --}}
            <div
                class="absolute top-full right-4
                       w-3 h-3
                       bg-white border-r border-b border-slate-200
                       rotate-45 -mt-1">
            </div>
        </div>

        {{-- ＋ボタン（右上） --}}
        <button
            type="button"
            @mouseenter="open = true"
            @mouseleave="open = false"
            @click="
        if (window.innerWidth < 640 && !open) {
            open = true;
        } else {
            window.location.href = '{{ route('goals.create') }}';
        }
    "
            class="absolute top-4 right-4
           w-10 h-10 flex items-center justify-center
           rounded-full bg-indigo-500 text-white text-xl font-bold
           hover:bg-indigo-600 active:scale-95 transition shadow-md">
            +
        </button>


        <h3 class="text-lg font-semibold text-slate-800 mb-4">
            📝 最近の記録
        </h3>

        @if($recentActivities->count())
        <div class="space-y-4">
            @foreach($recentActivities as $activity)
            <div class="border border-slate-200 rounded-xl p-5">
                <div class="flex justify-between">

                    <div>
                        @if($activity->type === 'goal')
                        <p class="text-sm font-semibold text-indigo-600">🎯 活動記録</p>
                        @elseif($activity->type === 'mission')
                        <p class="text-sm font-semibold text-green-600">🎉 ミッション達成</p>
                        @endif

                        <p class="text-slate-800 font-medium mt-1">
                            {{ $activity->title }}
                        </p>

                        <p class="text-xs text-slate-400 mt-1">
                            活動日：{{ optional($activity->date)->format('Y-m-d') }}
                        </p>
                    </div>

                    @if($activity->type === 'goal' && $activity->url)
                    <a href="{{ $activity->url }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        編集
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('activities.index') }}"
            class="inline-block px-4 py-2 rounded-lg
                              border border-indigo-600 text-indigo-600
                              font-semibold text-sm
                              hover:bg-indigo-600 hover:text-white transition">
            すべての活動を見る
        </a>
    </div>
</div>

{{-- Chart.js でグラフ表示 --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weeklyActivityChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!!json_encode($weeklyLabels) !!},
            datasets: [{
                label: '活動件数',
                data: {!!json_encode($weeklyData) !!},
                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

@endsection