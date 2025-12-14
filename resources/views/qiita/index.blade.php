@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    {{-- ヘッダー --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">みんなのQiitaタイムライン</h1>
            <p class="text-sm text-gray-500 mt-1">
                メンバーがミッションとして登録したQiita記事の一覧です。
            </p>
        </div>
        <div class="text-3xl">📝</div>
    </div>

    {{-- タイムライン --}}
    <div class="space-y-4">
        @forelse ($articles as $article)
            <div class="flex gap-3">

                {{-- 左：ユーザーアイコン風 --}}
                <div class="flex flex-col items-center">
                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-600">
                        {{ mb_substr($article->user->name, 0, 2) }}
                    </div>
                    <div class="flex-1 w-px bg-gray-200 mt-1"></div>
                </div>

                {{-- 右：カード本体 --}}
                <div class="flex-1 rounded-2xl border bg-white px-4 py-3 shadow-sm space-y-2">

                    {{-- 上段：ユーザー名 + 日付 --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $article->user->name }}
                            </p>
                            <p class="text-[11px] text-gray-500">
                                ミッション：技術ブログ（Qiita）を書く
                            </p>
                        </div>
                        <div class="text-right">
                            @if ($article->posted_at)
                                <p class="text-[11px] text-gray-400">
                                    投稿: {{ $article->posted_at->format('Y/m/d H:i') }}
                                </p>
                            @endif
                            <p class="text-[11px] text-gray-400">
                                登録: {{ $article->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    {{-- 記事タイトル --}}
                    <div>
                        <a href="{{ $article->url }}" target="_blank"
                           class="text-base font-semibold text-indigo-700 hover:underline break-words">
                            {{ $article->title }}
                        </a>
                    </div>

                    {{-- タグ --}}
                    @if (!empty($article->tags))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($article->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px]">
                                    #{{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- 本文の抜粋 --}}
                    <div class="text-xs text-gray-600 mt-1">
                        @php
                            $plain = Str::limit(strip_tags($article->body), 160);
                        @endphp
                        <p class="whitespace-pre-wrap">{{ $plain }}</p>
                    </div>

                    {{-- 下段：LGTM数 --}}
                    <div class="flex justify-between items-center text-[11px] text-gray-500 pt-1">
                        <span>
                            👍 LGTM: <span class="font-semibold text-gray-800">{{ $article->likes_count }}</span>
                        </span>
                        <span class="text-[11px] text-gray-400">
                            ID: {{ $article->item_id }}
                        </span>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center text-sm text-gray-500 py-10">
                まだQiita記事は登録されていません。
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div>
        {{ $articles->links() }}
    </div>

</div>
@endsection
