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


        {{-- ================= 回答期限 ================= --}}
        @if($company)
        <form method="POST" action="{{ route('admin.rewards.deadline') }}" class="mb-6">
            @csrf
            <div class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        回答期限
                    </label>
                    <input
                        type="date"
                        name="end_at"
                        value="{{ $activeSurvey?->end_at?->format('Y-m-d') }}">
                </div>

                <button
                    class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900">
                    期限を設定
                </button>
            </div>
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

<style>
    .card-shadow {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
    }
</style>
@endsection