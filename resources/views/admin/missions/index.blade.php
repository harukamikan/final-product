@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    {{-- 上部バナー：色だけログイン画面に合わせる --}}
    <div class="rounded-3xl bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 px-6 py-5 text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">ミッション管理</h1>
            <p class="text-xs md:text-sm text-indigo-100 mt-1">
                Qiita 記事・イベント登壇・企画・資格取得などのミッションをここで設定します。
            </p>
        </div>
        <a href="{{ route('admin.missions.create') }}"
           class="inline-flex items-center px-4 py-2 rounded-full bg-white/90 text-xs font-semibold text-purple-700 shadow hover:bg-white">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4v16m8-8H4"/>
            </svg>
            新しいミッションを作成
        </a>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 検索・フィルタ：よく触る操作を上にまとめる --}}
    <form method="GET"
          class="rounded-2xl bg-white shadow-sm border px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <div class="flex-1 flex items-center gap-2">
            {{-- キーワード検索 --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </span>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="タイトル・キーで検索"
                       class="w-full pl-9 pr-3 py-2 rounded-xl border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-xs">
            </div>

            {{-- トリガー種別フィルタ --}}
            <select name="trigger_type"
                    class="rounded-xl border-gray-300 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">全トリガー</option>
                <option value="tech_blog_posted" @selected(request('trigger_type') === 'tech_blog_posted')>
                    技術ブログ（Qiita）
                </option>
                <option value="manual_event_speaker" @selected(request('trigger_type') === 'manual_event_speaker')>
                    イベント登壇
                </option>
                <option value="manual_event_owner" @selected(request('trigger_type') === 'manual_event_owner')>
                    イベント企画
                </option>
                <option value="manual_certification" @selected(request('trigger_type') === 'manual_certification')>
                    資格取得
                </option>
            </select>

            {{-- 繰り返しフィルタ（任意） --}}
            <select name="repeatable"
                    class="rounded-xl border-gray-300 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">一括</option>
                <option value="1" @selected(request('repeatable') === '1')>繰り返し可のみ</option>
                <option value="0" @selected(request('repeatable') === '0')>一度きりのみ</option>
            </select>
        </div>

        <div class="flex items-center gap-2 justify-end">
            <a href="{{ route('admin.missions.index') }}"
               class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50">
                クリア
            </a>
            <button type="submit"
                    class="px-4 py-1.5 rounded-full bg-purple-600 text-white font-semibold hover:bg-purple-700">
                絞り込み
            </button>
        </div>
    </form>

    {{-- サマリー（「今どうなっているか」を即確認） --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        <div class="rounded-2xl border bg-white px-4 py-3 shadow-sm flex items-center justify-between">
            <span class="text-gray-500">ミッション数</span>
            <span class="text-base font-semibold text-gray-900">{{ $missions->total() }}</span>
        </div>
        <div class="rounded-2xl border bg-white px-4 py-3 shadow-sm flex items-center justify-between">
            <span class="text-gray-500">繰り返し可能</span>
            <span class="text-base font-semibold text-gray-900">{{ $repeatableCount ?? 0 }}</span>
        </div>
        <div class="rounded-2xl border bg-white px-4 py-3 shadow-sm flex items-center justify-between">
            <span class="text-gray-500">平均報酬マイル</span>
            <span class="text-base font-semibold text-gray-900">{{ $avgRewardMiles ?? 0 }}</span>
        </div>
    </div>

    {{-- テーブル：情報量は多いが読みやすく --}}
    <div class="rounded-2xl bg-white border shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">ID</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">タイトル</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">キー</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">トリガー</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500">回数</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500">報酬</th>
                    <th class="px-3 py-2 text-center font-medium text-gray-500">タイプ</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($missions as $mission)
                <tr class="hover:bg-gray-50/70 transition">
                    <td class="px-3 py-2 text-gray-500">
                        #{{ $mission->id }}
                    </td>
                    <td class="px-3 py-2 text-gray-900">
                        <div class="font-semibold">{{ $mission->title }}</div>
                        @if($mission->description)
                            <div class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">
                                {{ $mission->description }}
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-2 font-mono text-[11px] text-gray-500">
                        {{ $mission->key }}
                    </td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                            @class([
                                'bg-emerald-50 text-emerald-700' => $mission->trigger_type === 'tech_blog_posted',
                                'bg-indigo-50 text-indigo-700' => str_starts_with($mission->trigger_type, 'manual_event'),
                                'bg-purple-50 text-purple-700' => $mission->trigger_type === 'manual_certification',
                                'bg-gray-100 text-gray-600' => ! in_array($mission->trigger_type, [
                                    'tech_blog_posted',
                                    'manual_event_speaker',
                                    'manual_event_owner',
                                    'manual_certification',
                                ]),
                            ])">
                            {{ $mission->trigger_type }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-right text-gray-700">
                        {{ $mission->required_count }}
                    </td>
                    <td class="px-3 py-2 text-right text-gray-700">
                        {{ $mission->reward_miles }}
                    </td>
                    <td class="px-3 py-2 text-center">
                        @if($mission->repeatable)
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px]">
                                繰り返し
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px]">
                                一度きり
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('admin.missions.edit', $mission) }}"
                               class="px-2 py-1 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100 text-[11px]">
                                編集
                            </a>
                            <form action="{{ route('admin.missions.destroy', $mission) }}"
                                  method="POST"
                                  onsubmit="return confirm('削除してよろしいですか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-2 py-1 rounded-full bg-red-50 text-red-600 hover:bg-red-100 text-[11px]">
                                    削除
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-xs text-gray-500">
                        条件に一致するミッションがありません。
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $missions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
