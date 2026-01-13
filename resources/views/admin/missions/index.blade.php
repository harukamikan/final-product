@extends('layouts.admin')

@section('content')
<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ===== ヘッダ ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    ミッション管理
                </h1>
                <p class="text-sm text-slate-700 mt-1">
                    ミッションの作成・編集・管理を行います
                </p>
            </div>

            <a href="{{ route('admin.missions.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                      bg-indigo-600 text-white text-sm font-semibold
                      hover:bg-indigo-700 transition">
                ＋ 新しいミッション
            </a>
        </div>

        {{-- ===== フラッシュ ===== --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- ===== 検索・フィルタ ===== --}}
        <form method="GET"
              class="bg-white rounded-2xl shadow-sm p-4 flex flex-col gap-3 md:flex-row md:items-center">

            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="タイトル・キーで検索"
                   class="w-full md:flex-[2] rounded-lg border-gray-300 px-3 py-2 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

            <select name="trigger_type"
                    class="w-full md:w-40 rounded-lg border-gray-300 px-3 py-2 text-sm
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">全トリガー</option>
                <option value="tech_blog_posted" @selected(request('trigger_type') === 'tech_blog_posted')>技術ブログ</option>
                <option value="manual_event_speaker" @selected(request('trigger_type') === 'manual_event_speaker')>イベント登壇</option>
                <option value="manual_event_owner" @selected(request('trigger_type') === 'manual_event_owner')>イベント企画</option>
                <option value="manual_certification" @selected(request('trigger_type') === 'manual_certification')>資格取得</option>
            </select>

            <select name="repeatable"
                    class="w-full md:w-40 rounded-lg border-gray-300 px-3 py-2 text-sm
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">全タイプ</option>
                <option value="1" @selected(request('repeatable') === '1')>繰り返し可</option>
                <option value="0" @selected(request('repeatable') === '0')>一度きり</option>
            </select>

            <div class="flex gap-2">
                <a href="{{ route('admin.missions.index') }}"
                   class="flex-1 md:flex-none text-center px-3 py-2 text-sm text-gray-600 hover:text-gray-800 underline">
                    クリア
                </a>
                <button type="submit"
                        class="flex-1 md:flex-none px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-sm transition">
                    絞り込み
                </button>
            </div>
        </form>

        {{-- ===== サマリー ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-sm p-5 h-28 flex flex-col justify-center items-center">
                <div class="text-xs text-slate-500 mb-1">ミッション数</div>
                <div class="text-3xl font-bold text-slate-800">{{ $missions->total() }}</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 h-28 flex flex-col justify-center items-center">
                <div class="text-xs text-slate-500 mb-1">繰り返し可能</div>
                <div class="text-3xl font-bold text-slate-800">{{ $repeatableCount ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 h-28 flex flex-col justify-center items-center">
                <div class="text-xs text-slate-500 mb-1">平均報酬</div>
                <div class="text-3xl font-bold text-slate-800">{{ $avgRewardMiles ?? 0 }}<span class="text-sm text-slate-500 ml-1 font-normal">マイル</span></div>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- スマホ：カード表示 --}}
        {{-- ========================= --}}
        <div class="space-y-4 md:hidden">
            @forelse($missions as $mission)
                <div class="bg-white rounded-2xl shadow-md p-4 space-y-3">
                    <div>
                        <p class="font-bold text-slate-800 text-base">
                            {{ $mission->title }}
                        </p>
                        @if($mission->description)
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $mission->description }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if($mission->repeatable)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                繰り返し可能
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                一度きり
                            </span>
                        @endif
                    </div>

                    <div class="flex justify-between text-sm text-slate-600 bg-slate-50 rounded-lg p-2">
                        <span>回数：<span class="font-semibold text-slate-800">{{ $mission->required_count }}</span></span>
                        <span>報酬：<span class="font-semibold text-slate-800">{{ $mission->reward_miles }}</span></span>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <a href="{{ route('admin.missions.edit', $mission) }}"
                           class="flex-1 text-center px-4 py-2 rounded-lg border border-indigo-600 text-indigo-600 text-sm font-medium hover:bg-indigo-600 hover:text-white transition">
                            編集
                        </a>
                        <form action="{{ route('admin.missions.destroy', $mission) }}"
                              method="POST"
                              class="flex-1"
                              onsubmit="return confirm('このミッションを削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded-lg bg-red-50 border border-red-300 text-red-700 text-sm font-medium hover:bg-red-100 transition">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                    <div class="text-slate-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <p class="text-slate-600 text-sm mb-4">ミッションがありません</p>
                    <a href="{{ route('admin.missions.create') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                        ＋ 新しいミッション
                    </a>
                </div>
            @endforelse

            {{ $missions->links() }}
        </div>

        {{-- ========================= --}}
        {{-- PC：テーブル表示 --}}
        {{-- ========================= --}}
        <div class="hidden md:block bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-slate-100/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">タイトル</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600">回数</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600">報酬</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-600">タイプ</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($missions as $mission)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ $mission->title }}</div>
                            @if($mission->description)
                                <div class="text-xs text-slate-500 mt-0.5">{{ $mission->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700 tabular-nums">{{ $mission->required_count }}</td>
                        <td class="px-4 py-3 text-right text-slate-700 tabular-nums">{{ $mission->reward_miles }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mission->repeatable)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    繰り返し可能
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    一度きり
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.missions.edit', $mission) }}"
                               class="inline-flex items-center px-4 py-2 border border-indigo-600 rounded-lg text-xs font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                                編集
                            </a>
                            <form action="{{ route('admin.missions.destroy', $mission) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('このミッションを削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-300 rounded-lg text-xs font-medium text-red-700 hover:bg-red-100 transition">
                                    削除
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <div class="text-slate-400 mb-4">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <p class="text-slate-600 text-sm mb-4">ミッションがありません</p>
                            <a href="{{ route('admin.missions.create') }}"
                               class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                                ＋ 新しいミッション
                            </a>
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
</div>
@endsection
