@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- ヘッダー --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            会社タイムライン
        </h1>
        <p class="text-sm text-gray-500 mt-2">
            メンバーの技術的成果を共有
        </p>
    </div>

    {{-- タブナビゲーション --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-4 overflow-x-auto">
            <a href="{{ route('timeline.index', ['type' => 'all']) }}"
               class="px-4 py-2 font-semibold border-b-2 transition whitespace-nowrap {{ $currentType === 'all' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                すべて
            </a>
            <a href="{{ route('timeline.index', ['type' => 'qiita']) }}"
               class="px-4 py-2 font-semibold border-b-2 transition whitespace-nowrap {{ $currentType === 'qiita' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Qiita
            </a>
            <a href="{{ route('timeline.index', ['type' => 'event_hosting']) }}"
               class="px-4 py-2 font-semibold border-b-2 transition whitespace-nowrap {{ $currentType === 'event_hosting' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                イベント企画
            </a>
            <a href="{{ route('timeline.index', ['type' => 'event_speaking']) }}"
               class="px-4 py-2 font-semibold border-b-2 transition whitespace-nowrap {{ $currentType === 'event_speaking' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                イベント登壇
            </a>
            <a href="{{ route('timeline.index', ['type' => 'certification']) }}"
               class="px-4 py-2 font-semibold border-b-2 transition whitespace-nowrap {{ $currentType === 'certification' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                資格取得
            </a>
        </nav>
    </div>

    {{-- 成功メッセージ --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- タイムライン表示（2カラムグリッド） --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse ($items as $item)
            @if (isset($item->type))
                {{-- 統合タイムライン（type プロパティあり） --}}
                @if ($item->type === 'qiita')
                    @include('timeline.partials.qiita', ['article' => $item->data])
                @elseif ($item->type === 'event_hosting')
                    @include('timeline.partials.event', ['event' => $item->data, 'eventType' => 'hosting'])
                @elseif ($item->type === 'event_speaking')
                    @include('timeline.partials.event', ['event' => $item->data, 'eventType' => 'speaking'])
                @elseif ($item->type === 'certification')
                    @include('timeline.partials.certification', ['cert' => $item->data])
                @endif
            @else
                {{-- 単一種別のタイムライン（Paginator から取得） --}}
                @if ($currentType === 'qiita')
                    @include('timeline.partials.qiita', ['article' => $item])
                @elseif ($currentType === 'event_hosting')
                    @include('timeline.partials.event', ['event' => $item, 'eventType' => 'hosting'])
                @elseif ($currentType === 'event_speaking')
                    @include('timeline.partials.event', ['event' => $item, 'eventType' => 'speaking'])
                @elseif ($currentType === 'certification')
                    @include('timeline.partials.certification', ['cert' => $item])
                @endif
            @endif
        @empty
            <div class="col-span-full text-center py-20">
                <div class="text-6xl mb-4">📝</div>
                <p class="text-lg text-gray-500">まだ投稿がありません</p>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    @if ($items->hasPages())
        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
