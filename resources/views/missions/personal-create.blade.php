@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">個人ミッションを追加</h1>

    {{-- 制限チェック --}}
    @if (!$canAdd)
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 mb-8 text-red-800">
            <p class="font-bold">⚠️ 上限に達しました</p>
            <p class="text-sm mt-2">個人ミッションは最大 {{ $maxPersonalMissions }} 個までです。</p>
            <p class="text-sm">現在: {{ $personalMissionsCount }} / {{ $maxPersonalMissions }}</p>
        </div>
        <a href="{{ route('missions.personal') }}" class="text-indigo-600 hover:underline">戻る</a>
    @else
        <form action="{{ route('personal-missions.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            {{-- 現在の半期目標 --}}
            @if ($currentSemesterGoal)
                <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <p class="text-sm text-indigo-600">現在の半期目標</p>
                    <p class="font-semibold text-indigo-900">{{ $currentSemesterGoal->title }}</p>
                </div>
            @endif

            {{-- タイトル --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    ミッション名
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="例：ブログを5本書く">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 説明 --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    説明（任意）
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="詳細な説明があれば..."></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 必要回数 --}}
            <div>
                <label for="required_count" class="block text-sm font-medium text-gray-700 mb-2">
                    達成に必要な回数
                </label>
                <input
                    type="number"
                    id="required_count"
                    name="required_count"
                    value="{{ old('required_count', 1) }}"
                    min="1"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('required_count')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ボタン --}}
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                    追加する
                </button>
                <a href="{{ route('missions.personal') }}"
                   class="px-6 py-2 bg-gray-200 text-gray-900 rounded-lg font-medium hover:bg-gray-300 transition">
                    キャンセル
                </a>
            </div>
        </form>
    @endif
</div>
@endsection