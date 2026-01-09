@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-10">

    {{-- ================= 活動一覧 ================= --}}
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-800">
            活動一覧
        </h1>

        @if($activities->count())
        <div class="space-y-4">
            @foreach($activities as $activity)
<div class="bg-white border border-slate-200 rounded-xl p-5">
    <div class="flex justify-between gap-4">

        {{-- 左：内容 --}}
        <div>
            @if($activity['type'] === 'mission')
                <p class="text-sm font-semibold text-green-600">
                    🎯 ミッション達成：{{ $activity['data']->mission->title }}
                </p>
            @else
                <p class="text-sm font-semibold text-indigo-600">
                    目標を追加しました：{{ $activity['data']->title }}
                </p>
            @endif

            <p class="text-xs text-slate-400 mt-1">
                作成日：{{ $activity['created_at']->format('Y-m-d') }}
            </p>
        </div>

        {{-- 右：操作（goal のみ） --}}
        @if($activity['type'] === 'goal')
        <div class="flex items-center gap-3">
            <a href="{{ route('goals.edit', $activity['data']->id) }}"
               class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                編集
            </a>

            <form action="{{ route('goals.destroy', $activity['data']->id) }}"
                  method="POST"
                  onsubmit="return confirm('この活動を削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-sm font-semibold text-red-500 hover:text-red-700">
                    削除
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endforeach

        </div>

        <div class="mt-6">
            {{ $activities->links() }}
        </div>
        @else
        <div class="bg-white rounded-xl shadow p-6 text-center text-slate-500">
            活動履歴がありません
        </div>
        @endif
    </div>

    {{-- ================= 過去の半期目標 ================= --}}
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-slate-800">
            📘 過去の半期目標
        </h2>

        @if($pastSemesterGoals->count())
        <div class="space-y-4">
            @foreach($pastSemesterGoals as $goal)
            <div class="bg-white border border-slate-200 rounded-xl p-5">
                <p class="font-semibold text-slate-800">
                    {{ $goal->title }}
                </p>

                <p class="text-sm text-slate-500 mt-1">
                    期限：
                    {{ optional($goal->deadline)->format('Y-m-d') }}
                </p>


                <div class="mt-3">
                    @if(!$goal->is_current)
                    <span class="inline-block px-3 py-1 text-xs font-semibold
                 bg-gray-100 text-gray-600 rounded-full">
                        過去の目標
                    </span>
                    @else
                    <span class="inline-block px-3 py-1 text-xs font-semibold
                 bg-blue-100 text-blue-700 rounded-full">
                        今期の目標
                    </span>
                    @endif

                </div>

                @if($goal->review)
                <p class="text-sm text-slate-600 mt-3">
                    振り返り：{{ $goal->review }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-xl shadow p-6 text-center text-slate-500">
            過去の半期目標はありません
        </div>
        @endif
    </div>

</div>
@endsection