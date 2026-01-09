@extends('layouts.app')

@section('content')
{{-- ミッション達成モーダル --}}
@include('components.mission-completion-modal', ['achievementData' => $achievementData ?? []])

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

{{-- 完了メッセージ & マイル --}}
    <div class="rounded-3xl bg-emerald-50 border border-emerald-100 px-5 py-4 flex justify-between items-center">
        <div>
            <p class="text-sm text-emerald-700 font-medium">
                技術ブログのURLを登録しました 🎉
            </p>
            @if (($achievementData['earned_miles'] ?? 0) > 0)
                <p class="text-xs text-emerald-700 mt-1">
                    このミッションで <span class="font-bold">{{ $achievementData['earned_miles'] }} mile</span> を獲得しました。
                </p>
            @endif
            
            {{-- デバッグ情報（開発時のみ） --}}
            @if(config('app.debug'))
                <details class="mt-2 text-xs text-gray-600">
                    <summary class="cursor-pointer hover:text-indigo-600">デバッグ情報を表示</summary>
                    <pre class="mt-1 p-2 bg-gray-100 rounded overflow-auto">{{ json_encode($achievementData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endif
        </div>
        <div class="text-3xl">📝</div>
    </div>

    {{-- Qiita記事の概要 --}}
    <div class="rounded-3xl border bg-white shadow-sm px-5 py-6 space-y-4">
        <div>
            <p class="text-xs text-gray-500">紐づくミッション</p>
            <p class="text-sm font-semibold text-gray-900">
                {{ $mission->title }}
            </p>
        </div>

        <div class="border-t pt-4 space-y-3">
            <div>
                <p class="text-xs text-gray-500 mb-1">記事タイトル</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $qiita['title'] }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500 mb-1">タグ</p>
                @if (!empty($qiita['tags']))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($qiita['tags'] as $tag)
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                                #{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400">タグ情報はありません</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                <span>LGTM数: <span class="font-semibold text-gray-800">{{ $qiita['likes_count'] }}</span></span>
                @if ($qiita['created_at'])
                    <span>投稿日時: 
                        <span class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($qiita['created_at'])->format('Y/m/d H:i') }}
                        </span>
                    </span>
                @endif
            </div>

            <div>
                <p class="text-xs text-gray-500 mb-1">本文（Markdown）</p>
                <pre class="text-xs bg-gray-50 rounded-2xl p-3 overflow-auto max-h-64 whitespace-pre-wrap">{{ $qiita['body'] }}</pre>
            </div>

            <div>
                <a href="{{ $qiita['url'] }}"
                   target="_blank"
                   class="inline-flex items-center text-xs text-indigo-600 hover:underline">
                    Qiitaで記事を開く
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14 3h7m0 0v7m0-7L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- 戻るボタン --}}
    <div class="flex justify-end">
        <a href="{{ route('missions.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-900 text-white text-sm font-medium hover:bg-black">
            ミッション一覧に戻る
        </a>
    </div>

</div>
@endsection
