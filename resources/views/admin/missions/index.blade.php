@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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
              class="bg-white rounded-2xl shadow-md p-4 flex flex-col gap-3 md:flex-row md:items-center">

            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="タイトル・キーで検索"
                   class="w-full md:flex-1 rounded-lg border-gray-300 px-3 py-2 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

            <select name="trigger_type"
                    class="w-full md:w-auto rounded-lg border-gray-300 px-3 py-2 text-sm">
                <option value="">全トリガー</option>
                <option value="tech_blog_posted" @selected(request('trigger_type') === 'tech_blog_posted')>技術ブログ</option>
                <option value="manual_event_speaker" @selected(request('trigger_type') === 'manual_event_speaker')>イベント登壇</option>
                <option value="manual_event_owner" @selected(request('trigger_type') === 'manual_event_owner')>イベント企画</option>
                <option value="manual_certification" @selected(request('trigger_type') === 'manual_certification')>資格取得</option>
            </select>

            <select name="repeatable"
                    class="w-full md:w-auto rounded-lg border-gray-300 px-3 py-2 text-sm">
                <option value="">全タイプ</option>
                <option value="1" @selected(request('repeatable') === '1')>繰り返し可</option>
                <option value="0" @selected(request('repeatable') === '0')>一度きり</option>
            </select>

            <div class="flex gap-2">
                <a href="{{ route('admin.missions.index') }}"
                   class="flex-1 md:flex-none text-center px-3 py-2 rounded-lg border text-sm text-slate-600">
                    クリア
                </a>
                <button type="submit"
                        class="flex-1 md:flex-none px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold">
                    絞り込み
                </button>
            </div>
        </form>

        {{-- ===== サマリー ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-md p-4 flex justify-between">
                <span class="text-sm text-slate-500">ミッション数</span>
                <span class="font-semibold">{{ $missions->total() }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-4 flex justify-between">
                <span class="text-sm text-slate-500">繰り返し可能</span>
                <span class="font-semibold">{{ $repeatableCount ?? 0 }}</span>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-4 flex justify-between">
                <span class="text-sm text-slate-500">平均報酬</span>
                <span class="font-semibold">{{ $avgRewardMiles ?? 0 }}</span>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- スマホ：カード表示 --}}
        {{-- ========================= --}}
        <div class="space-y-4 md:hidden">
            @forelse($missions as $mission)
                <div class="bg-white rounded-2xl shadow-md p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-slate-800">
                                {{ $mission->title }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $mission->key }}
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100">
                            #{{ $mission->id }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                            {{ $mission->trigger_type }}
                        </span>

                        @if($mission->repeatable)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                繰り返し
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                一度きり
                            </span>
                        @endif
                    </div>

                    <div class="flex justify-between text-sm text-slate-600">
                        <span>回数：{{ $mission->required_count }}</span>
                        <span>報酬：{{ $mission->reward_miles }}</span>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <a href="{{ route('admin.missions.edit', $mission) }}"
                           class="flex-1 text-center px-3 py-2 rounded-lg border border-indigo-600 text-indigo-600 text-sm">
                            編集
                        </a>
                        <form action="{{ route('admin.missions.destroy', $mission) }}"
                              method="POST"
                              class="flex-1"
                              onsubmit="return confirm('削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full px-3 py-2 rounded-lg border border-red-500 text-red-600 text-sm">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-slate-500 text-sm">
                    ミッションがありません
                </div>
            @endforelse

            {{ $missions->links() }}
        </div>

        {{-- ========================= --}}
        {{-- PC：テーブル表示 --}}
        {{-- ========================= --}}
        <div class="hidden md:block bg-white rounded-2xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y text-sm">
                <thead class="bg-gray-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">タイトル</th>
                        <th class="px-4 py-2">キー</th>
                        <th class="px-4 py-2">トリガー</th>
                        <th class="px-4 py-2 text-right">回数</th>
                        <th class="px-4 py-2 text-right">報酬</th>
                        <th class="px-4 py-2 text-center">タイプ</th>
                        <th class="px-4 py-2 text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @foreach($missions as $mission)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-500">#{{ $mission->id }}</td>
                        <td class="px-4 py-2 font-semibold">{{ $mission->title }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $mission->key }}</td>
                        <td class="px-4 py-2">{{ $mission->trigger_type }}</td>
                        <td class="px-4 py-2 text-right">{{ $mission->required_count }}</td>
                        <td class="px-4 py-2 text-right">{{ $mission->reward_miles }}</td>
                        <td class="px-4 py-2 text-center">
                            {{ $mission->repeatable ? '繰り返し' : '一度きり' }}
                        </td>
                        <td class="px-4 py-2 text-right space-x-1">
                            <a href="{{ route('admin.missions.edit', $mission) }}"
                               class="px-2 py-1 border rounded-lg text-xs text-indigo-600 border-indigo-600">
                                編集
                            </a>
                            <form action="{{ route('admin.missions.destroy', $mission) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button class="px-2 py-1 border rounded-lg text-xs text-red-600 border-red-500">
                                    削除
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="px-4 py-3 border-t bg-gray-50">
                {{ $missions->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
