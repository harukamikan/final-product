@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- ページタイトル --}}
    <h1 class="text-2xl font-bold mb-6 text-center sm:text-left">
        活動を編集
    </h1>

    {{-- 実際に更新できるフォーム --}}
    <form action="{{ route('goals.update', $goal) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow space-y-6">
        @csrf
        @method('PUT')

        {{-- 活動タイトル --}}
        <div>
            <label class="block font-semibold mb-2">活動タイトル</label>
            <input type="text"
                   name="title"
                   value="{{ old('title', $goal->title) }}"
                   class="w-full border rounded-lg p-3 text-sm sm:text-base"
                   placeholder="例：TOEIC800点取得"
                   required>
            @error('title')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 活動日 --}}
        <div>
            <label class="block font-semibold mb-2">活動日</label>
            <input type="date"
                   name="activity_date"
                   value="{{ old('activity_date', optional($activityDate)->format('Y-m-d')) }}"
                   class="w-full border rounded-lg p-3 text-sm sm:text-base"
                   required>
            @error('activity_date')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- メモ --}}
        <div>
            <label class="block font-semibold mb-2">メモ</label>
            <textarea name="memo"
                      class="w-full border rounded-lg p-3 text-sm sm:text-base"
                      rows="4"
                      placeholder="補足情報などあれば入力してください">{{ old('memo', $goal->memo) }}</textarea>
            @error('memo')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- 保存ボタン --}}
        <div class="text-center sm:text-right pt-4">
            <button
                class="bg-blue-500 text-white px-6 py-3 rounded-lg
                       text-sm sm:text-base
                       hover:bg-blue-600 transition">
                保存する
            </button>
        </div>

    </form>

</div>
@endsection
