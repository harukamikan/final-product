@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

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
            @php
                $hasSummary = !empty($article->summary);
                // 本文をそのまま出すとMarkdownが見づらいので、まずはプレーンに（要望が「全文表示」なので）
                $fullBody = trim(strip_tags($article->body ?? ''));
            @endphp

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
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ $article->url }}" target="_blank"
                           class="text-base font-semibold text-indigo-700 hover:underline break-words">
                            {{ $article->title }}
                        </a>

                        {{-- 状態バッジ（ここで一目で分かる） --}}
                        @if ($hasSummary)
                            <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-100">
                                ✨ 要約あり
                            </span>
                        @else
                            <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold border border-amber-100">
                                ⚠ 要約未生成
                            </span>
                        @endif
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

                    {{-- 本文表示領域 --}}
                    <div class="text-xs text-gray-700 mt-2 space-y-2">

                        {{-- 要約がある場合 --}}
                        @if ($hasSummary)
                            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3">
                                <p class="text-[11px] font-semibold text-indigo-700 mb-1">AI要約</p>
                                <p class="whitespace-pre-wrap">{{ $article->summary }}</p>
                            </div>

                            {{-- 参考として本文は折りたたみ（クリックで読める） --}}
                            <details class="rounded-xl border bg-white px-3 py-2">
                                <summary class="cursor-pointer text-[11px] text-gray-500 select-none">
                                    本文を全文表示（クリックで開く）
                                </summary>
                                <pre class="mt-2 text-[11px] whitespace-pre-wrap text-gray-700">{{ $fullBody ?: '本文がありません' }}</pre>
                            </details>

                        {{-- 要約がない場合（要望どおり「全文」を見せる） --}}
                        @else
                            <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                                <p class="text-[11px] font-semibold text-amber-700 mb-1">要約未生成のため本文を全文表示</p>
                                <pre class="text-[11px] whitespace-pre-wrap text-gray-800">{{ $fullBody ?: '本文がありません' }}</pre>
                            </div>
                        @endif
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
