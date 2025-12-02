{{-- resources/views/goals/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- ページタイトル --}}
    <h1 class="text-2xl font-bold mb-6 text-center sm:text-left">
        目標を追加する
    </h1>

    {{-- カードコンテナ --}}
    <div class="bg-white p-6 rounded-xl shadow space-y-6">

        {{-- 目標タイトル --}}
        <div>
            <label class="block font-semibold mb-2">目標タイトル</label>
            <input type="text"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：TOEIC800点取得">
        </div>

        {{-- 期限 --}}
        <div>
            <label class="block font-semibold mb-2">期限</label>
            <input type="date"
                class="w-full border rounded-lg p-3 text-sm sm:text-base">
        </div>

        {{-- カテゴリ --}}
        <div>
            <label class="block font-semibold mb-2">カテゴリ</label>
            <select class="w-full border rounded-lg p-3 text-sm sm:text-base">
                <option>選択してください</option>
                <option>学習</option>
                <option>健康</option>
                <option>お金管理</option>
                <option>生活習慣</option>
                <option>趣味</option>
                <option>その他</option>
            </select>
        </div>

        {{-- 数値目標 --}}
        <div>
            <label class="block font-semibold mb-2">数値目標</label>
            <input type="text"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：毎日英単語100語／月20回ジムなど">
        </div>

        {{-- 現状値 --}}
        <div>
            <label class="block font-semibold mb-2">現状値（初期値）</label>
            <input type="number"
                class="w-full border rounded-lg p-3 text-sm sm:text-base"
                placeholder="例：0">
        </div>

        {{-- 達成基準 --}}
        <div>
            <label class="block font-semibold mb-2">達成基準</label>
            <textarea class="w-full border rounded-lg p-3 text-sm sm:text-base"
                rows="3"
                placeholder="例：模試で780点以上を3回連続で取れたら完了とみなす"></textarea>
        </div>

        {{-- メモ --}}
        <div>
            <label class="block font-semibold mb-2">メモ</label>
            <textarea class="w-full border rounded-lg p-3 text-sm sm:text-base"
                rows="4"
                placeholder="補足情報などあれば入力してください"></textarea>
        </div>

        {{-- 保存ボタン（UIのみ） --}}
        <div class="text-center sm:text-right pt-4">
            <button
                class="bg-blue-500 text-white px-6 py-3 rounded-lg text-sm sm:text-base hover:bg-blue-600 transition">
                保存（まだ動作しません）
            </button>
        </div>

    </div>

</div>
@endsection
