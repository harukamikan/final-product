@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- ================= タイトル ================= --}}
        <h2 class="text-3xl font-bold mb-6 text-amber-100">
            🎁 報酬決定
        </h2>

        {{-- ================= アンケート ON / OFF ================= --}}
        @if($activeSurvey)
        {{-- アンケート停止 --}}
        <form method="POST" action="{{ route('admin.rewards.toggle') }}" class="mb-6">
            @csrf
            <button
                class="px-4 py-2 rounded-lg text-sm font-semibold
                   bg-red-600 text-white hover:bg-red-700">
                アンケートを停止する
            </button>
        </form>
        @else
        {{-- アンケート開始 --}}
        <form method="POST" action="{{ route('admin.rewards.toggle') }}" class="mb-6">
            @csrf
            <button
                class="px-4 py-2 rounded-lg text-sm font-semibold
                   bg-indigo-600 text-white hover:bg-indigo-700">
                アンケートを開始する
            </button>
        </form>
        @endif

        {{-- ================= メインカード ================= --}}
        <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg mt-8">
            <div class="p-6">

                <h3 class="font-semibold text-lg text-gray-800 mb-4">
                    📋 報酬アンケート結果（人気順）
                </h3>

                {{-- ================= 集計 ================= --}}
                <div class="flex gap-6 mb-8 text-sm">
                    <div class="px-4 py-2 rounded-lg bg-green-50 text-green-700 font-semibold">
                        回答済み：{{ $answeredCount }} 人
                    </div>

                    <div class="px-4 py-2 rounded-lg bg-red-50 text-red-700 font-semibold">
                        未回答：{{ $unansweredCount }} 人
                    </div>
                </div>

                {{-- ================= 報酬別カード（社員情報なし） ================= --}}
                @if(empty($rewardStats))
                <p class="text-sm text-gray-500">
                    現在、アンケートの回答はありません。
                </p>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($rewardStats as $rewardName => $data)
                    <div class="bg-white rounded-xl shadow p-6 space-y-4 border">

                        <div class="flex justify-between items-center">
                            <h4 class="text-lg font-semibold text-gray-800">
                                🎁 {{ $rewardName }}
                            </h4>

                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">
                                希望 {{ $data['count'] }} 人
                            </span>
                        </div>

                        {{-- 一括採用ボタン --}}
                        <form method="POST"
                            action="{{ route('admin.rewards.bulk-decide') }}">
                            @csrf
                            <input type="hidden" name="reward_name" value="{{ $rewardName }}">

                            <button
                                class="w-full py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                                この報酬を採用する
                            </button>
                        </form>

                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

    </div>
</div>
{{-- ================= 報酬使用申請一覧 ================= --}}
        <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg mt-8">
            <div class="p-6">

                <h3 class="font-semibold text-lg text-gray-800 mb-4">
                    📝 ユーザー報酬一覧
                </h3>

                @php
                    $userRewards = \App\Models\UserReward::with(['user', 'reward'])
                        ->where('company_id', auth()->user()->company_id)
                        ->orderBy('used_at', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp

                @if($userRewards->isEmpty())
                    <p class="text-sm text-gray-500">
                        獲得された報酬はまだありません。
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ユーザー</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">報酬</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">使用状況</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">対応状況</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($userRewards as $ur)
                                    <tr class="{{ $ur->resolved_at ? 'bg-gray-50' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $ur->user->name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $ur->reward->name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($ur->used_at)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    🎉 使用済み
                                                </span>
                                                <div class="text-xs text-gray-400 mt-1">
                                                    {{ $ur->used_at->format('Y/m/d H:i') }}
                                                </div>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                    未使用
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($ur->used_at)
                                                @if($ur->resolved_at)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        ✅ 対応済み
                                                    </span>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        {{ $ur->resolved_at->format('Y/m/d H:i') }}
                                                    </div>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        ⏳ 未対応
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @if($ur->used_at)
                                                @if($ur->resolved_at)
                                                    <form method="POST" action="{{ route('admin.rewards.unresolve', $ur->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-gray-600 hover:text-gray-800 text-xs">
                                                            未対応に戻す
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.rewards.resolve', $ur->id) }}">
                                                        @csrf
                                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                                                            対応済み
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>

<style>
    .card-shadow {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
    }
</style>
@endsection