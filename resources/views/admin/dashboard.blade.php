@extends('layouts.admin')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold mb-6 text-amber-100">🔧 管理者ダッシュボード</h2>

            <!-- 統計サマリー -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-4 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">総ユーザー数</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $totalUsers }}</div>
                        <p class="text-gray-500 text-sm">登録ユーザー</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-4 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">総マイル数</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ number_format($totalMiles) }}</div>
                        <p class="text-gray-500 text-sm">全ユーザー合計</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-4 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">ランク分布</h3>
                        <div class="text-sm text-gray-700 mt-3">
                            <div class="flex justify-between mb-2">
                                <span>🥇 ゴールド:</span>
                                <span class="font-bold">{{ $rankCounts['ゴールド'] }}人</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>🥈 シルバー:</span>
                                <span class="font-bold">{{ $rankCounts['シルバー'] }}人</span>
                            </div>
                            <div class="flex justify-between">
                                <span>🥉 ブロンズ:</span>
                                <span class="font-bold">{{ $rankCounts['ブロンズ'] }}人</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- トップユーザー -->
            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">🏆 トップユーザー（マイル順）</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">順位</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザー名</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">マイル</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ランク</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topUsers as $index => $user)
                                    @php
                                        $miles = $user->mile_histories_sum_miles ?? 0;
                                        if ($miles >= 500) {
                                            $rank = '🥇 ゴールド';
                                            $rankColor = 'text-yellow-600';
                                        } elseif ($miles >= 200) {
                                            $rank = '🥈 シルバー';
                                            $rankColor = 'text-gray-400';
                                        } else {
                                            $rank = '🥉 ブロンズ';
                                            $rankColor = 'text-orange-600';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($miles) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $rankColor }} font-semibold">
                                            {{ $rank }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
@endsection
