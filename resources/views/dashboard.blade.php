@extends('layouts.app')

<style>
    .card-shadow {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
    }
</style>

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 text-pink-200">ようこそ、{{ auth()->user()->name }}さん！</h2>

            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">📋 今期の半期目標</h3>
                    <div class="border-l-4 border-indigo-500 bg-indigo-50 p-4 rounded-r">
                        <div class="text-gray-800 text-lg">技術ブログの執筆を通じて社内外への情報発信を強化し、エンジニアブランディングを向上させる</div>
                    </div>
                </div>
            </div>

            <!-- マイル統計 -->
            <div class="grid grid-cols-3 gap-2 mb-6">
                <style>
                    @media (max-width: 640px) {
                        .rank-text { font-size: 1.2rem !important; }
                        .rank-icon { font-size: 1.5rem; }
                    } 
                </style>
                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-6 text-center flex flex-col justify-center h-full">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2 text-center">総マイル</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $totalMiles }}</div>
                        <p class="text-gray-500 text-sm whitespace-nowrap">累計獲得マイル</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">現在のランク</h3>
                        <div class="rank-text font-bold mb-2 whitespace-nowrap flex items-center justify-center gap-1
                            @if($rank == 'ゴールド') text-yellow-400
                            @elseif($rank == 'シルバー') text-gray-400
                            @else text-orange-600
                            @endif">
                            <span class="rank-icon">
                                @if($rank == 'ゴールド') 🥇
                                @elseif($rank == 'シルバー') 🥈
                                @else 🥉
                                @endif
                            </span>
                            <span>{{ $rank }}</span>
                        </div>
                        
                        @php
                            $nextRank = $rank == 'ブロンズ' ? 'シルバー' : ($rank == 'シルバー' ? 'ゴールド' : '最高ランク');
                            $nextMiles = $rank == 'ブロンズ' ? 200 : ($rank == 'シルバー' ? 500 : 500);
                            $remaining = max(0, $nextMiles - $totalMiles);
                            $progress = min(100, ($totalMiles / $nextMiles) * 100);
                        @endphp
                        
                        @if($rank != 'ゴールド')
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                </div>
                                <p class="text-gray-600 text-sm">{{ $nextRank }}まで あと{{ $remaining }}マイル ({{ $totalMiles }}/{{ $nextMiles }})</p>
                                @if($recommendedMission)
                                <div class="mt-3 p-3 bg-yellow-50 border-l-4 border-yellow-500 rounded-r text-center">
                                    <p class="text-yellow-700 text-sm font-semibold">
                                        💡 {{ $recommendedMission->title }}
                                    </p>
                                    <p class="text-yellow-600 text-xs mt-1">
                                        +{{ $recommendedMission->reward_miles }}マイル獲得
                                    </p>
                                </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500 text-sm mt-2">最高ランク達成！🎉</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-6 text-center flex flex-col justify-center h-full">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2 text-center">今月の活動</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $thisMonthGoals }}</div>
                        <p class="text-gray-500 text-sm">件</p>
                    </div>
                </div>
            </div>

            <!-- 新しい活動を記録ボタン -->
            <div class="mb-6">
                <a href="/goals/create" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 border-2 border-indigo-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                ➕ 新しい活動を記録
                </a>
            </div>

            <!-- 最近の活動 -->
            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
            <div class="p-6">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">📝 最近の記録</h3>
        
                @if($recentGoals->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentGoals as $goal)
                            <!-- 目標カード -->
                            <div class="border-l-4 border-indigo-500 bg-white border-2 border-gray-100 p-5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $goal->category }}</div>
                                        <div class="text-gray-600 mt-1">{{ $goal->title }}</div>
                                        <div class="text-gray-500 text-sm mt-1">{{ $goal->deadline }}</div>
                                    </div>
                                    <div>
                                        <a href="/goals/{{ $goal->id }}/edit" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                                            編集
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p class="mb-4">まだ目標が登録されていません</p>
                        <a href="/goals/create" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                            最初の目標を作成
                        </a>
                    </div>
                @endif
            </div>
        </div>

                    <div class="mt-6 text-center">
                        <a href="/activities" class="inline-block px-4 py-2 bg-white text-indigo-600 border-2 border-indigo-600 rounded-md font-semibold text-sm hover:bg-indigo-600 hover:text-white transition-all">
                            すべての活動を見る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
