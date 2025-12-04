@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- ページタイトル --}}
    <h1 class="text-2xl font-bold mb-6 text-center sm:text-left">
        目標を追加する
    </h1>

    {{-- 実際に保存できるフォーム --}}
    <form action="{{ route('goals.store') }}" method="POST"
        class="bg-white p-6 rounded-xl shadow space-y-6">
        @csrf

        {{-- 目標タイトル --}}
        <div>
            <label class="block font-semibold mb-2">目標タイトル</label>
            <input type="text" name="title"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：TOEIC800点取得" required>
        </div>

        {{-- 期限 --}}
        <div>
            <label class="block font-semibold mb-2">期限</label>
            <input type="date" name="deadline"
                class="w-full border rounded-lg p-3 text-sm sm:text-base" required>
        </div>

        {{-- カテゴリ --}}
        <div>
            <label class="block font-semibold mb-2">カテゴリ</label>
            <select name="category"
                class="w-full border rounded-lg p-3 text-sm sm:text-base">
                <option value="">選択してください</option>
                <option value="学習">学習</option>
                <option value="健康">健康</option>
                <option value="お金管理">お金管理</option>
                <option value="生活習慣">生活習慣</option>
                <option value="趣味">趣味</option>
                <option value="その他">その他</option>
            </select>
        </div>

        {{-- 数値目標 --}}
        <div>
            <label class="block font-semibold mb-2">数値目標</label>
            <input type="text" name="target_value"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：毎日英単語100語／月20回ジムなど">
        </div>

        {{-- 現状値 --}}
        <div>
            <label class="block font-semibold mb-2">現状値（初期値）</label>
            <input type="number" name="current_value"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：0">
        </div>

        {{-- 達成基準 --}}
        <div>
            <label class="block font-semibold mb-2">達成基準</label>
            <textarea name="criteria"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                rows="3"
                placeholder="例：模試で780点以上を3回連続で取れたら完了とみなす"></textarea>
        </div>

        {{-- メモ --}}
        <div>
            <label class="block font-semibold mb-2">メモ</label>
            <textarea name="memo"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                rows="4"
                placeholder="補足情報などあれば入力してください"></textarea>
        </div>

        {{-- 保存ボタン --}}
        <div class="text-center sm:text-right pt-4">
            <button
                class="bg-blue-500 text-white px-6 py-3 rounded-lg text-sm sm:text-base hover:bg-blue-600 transition">
                保存する
            </button>
        </div>

    </form>

</div>
@endsection
