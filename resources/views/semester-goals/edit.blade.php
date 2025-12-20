@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 space-y-8">

        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                半期目標を編集
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                今期の目標を見直しましょう
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('semester-goals.update', $semesterGoal) }}"
            class="bg-white rounded-2xl shadow-md p-6 space-y-6"
        >
            @csrf
            @method('PUT')

            {{-- カテゴリ --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    カテゴリ
                </label>
                <input
                    type="text"
                    name="category"
                    value="{{ old('category', $semesterGoal->category) }}"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
            </div>

            {{-- タイトル --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    半期目標
                </label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $semesterGoal->title) }}"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
            </div>

            {{-- 詳細 --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    詳細
                </label>
                <textarea
                    name="description"
                    rows="4"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('description', $semesterGoal->description) }}</textarea>
            </div>

            {{-- 期限 --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">
                    期限
                </label>
                <input
                    type="date"
                    name="deadline"
                    value="{{ old('deadline', $semesterGoal->deadline) }}"
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- ボタン --}}
            <div class="flex justify-end gap-3 pt-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="px-4 py-2 rounded-lg border border-slate-300
                           text-slate-600 font-semibold text-sm
                           hover:bg-slate-100 transition">
                    戻る
                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg
                           bg-indigo-600 text-white
                           font-semibold text-sm
                           hover:bg-indigo-700 transition">
                    更新する
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
