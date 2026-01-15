{{-- resources/views/missions/blog_url.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-4 py-8 space-y-6">

    <div>
        <h1 class="text-xl font-semibold text-gray-900">技術ブログURLの送信</h1>
        <p class="text-sm text-gray-900 mt-1 font-medium">
            Qiitaなどに投稿した技術系ブログの記事URLを入力してください。送信するとミッションの進捗が更新されます。
        </p>
    </div>

    <form method="POST" action="{{ route('missions.blog-url.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">
                記事のURL
            </label>
            <input
                type="url"
                name="url"
                value="{{ old('url') }}"
                placeholder="https://qiita.com/..."
                class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            @error('url')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('missions.index') }}"
               class="text-xs text-gray-900 hover:underline font-medium">
                ← ミッション一覧に戻る
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                送信してミッションを更新
            </button>
        </div>
    </form>

</div>
@endsection
