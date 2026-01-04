@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow p-6 space-y-6">

        <h1 class="text-xl font-semibold text-gray-800">
            🎁 報酬アンケート
        </h1>

        <p class="text-sm text-gray-500">
            希望する報酬を第1〜第3希望まで入力してください。
            回答内容は管理者が確認のうえ報酬に反映されます。
        </p>

        <form method="POST"
            action="{{ route('reward-survey.store', $rewardSurvey) }}">

            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    第1希望（必須）
                </label>
                <input
                    type="text"
                    name="first_choice"
                    required
                    class="mt-1 w-full rounded-md border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    第2希望（必須）
                </label>
                <input
                    type="text"
                    name="second_choice"
                    required
                    class="mt-1 w-full rounded-md border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    第3希望（任意）
                </label>
                <input
                    type="text"
                    name="third_choice"
                    class="mt-1 w-full rounded-md border-gray-300">
            </div>

            <div class="flex justify-end pt-4">
                <button
                    class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    送信
                </button>
            </div>
        </form>

    </div>
</div>
@endsection