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
                @foreach($activities as $goal)
                    <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex justify-between gap-4">

                            {{-- 左：内容 --}}
                            <div>
                                <p class="text-sm font-semibold text-indigo-600">
                                    {{ $goal->category }}
                                </p>

                                <p class="text-slate-800 font-medium mt-1">
                                    {{ $goal->title }}
                                </p>

                                @if($goal->deadline)
                                    <p class="text-sm text-slate-500 mt-1">
                                        期限：{{ $goal->deadline->format('Y-m-d') }}
                                    </p>
                                @endif

                                <p class="text-xs text-slate-400 mt-2">
                                    作成日：{{ $goal->created_at->format('Y-m-d') }}
                                </p>
                            </div>

                            {{-- 右：操作 --}}
                            <div class="flex items-start gap-3">
                                <a href="{{ route('goals.edit', $goal) }}"
                                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                    編集
                                </a>

                                <form action="{{ route('goals.destroy', $goal) }}"
                                      method="POST"
                                      onsubmit="return confirm('この活動を削除します。よろしいですか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-sm font-semibold text-red-500 hover:text-red-700">
                                        削除
                                    </button>
                                </form>
                            </div>

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
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($pastSemesterGoals as $goal)
                    <div class="bg-white border border-slate-200 rounded-xl p-5">
                        <p class="font-semibold text-slate-800">
                            {{ $goal->title }}
                        </p>

                        <p class="text-sm text-slate-500 mt-1">
                            期間：
                            {{ $goal->start_date->format('Y-m-d') }}
                            〜
                            {{ $goal->end_date->format('Y-m-d') }}
                        </p>

                        <div class="mt-3">
                            @if($goal->status === 'completed')
                                <span class="inline-block px-3 py-1 text-xs font-semibold
                                             bg-green-100 text-green-700 rounded-full">
                                    達成
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 text-xs font-semibold
                                             bg-gray-100 text-gray-600 rounded-full">
                                    未達成
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
