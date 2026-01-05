@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">

    <h1 class="text-2xl font-bold text-slate-800">
        報酬配布管理
    </h1>

    {{-- 新規追加 --}}
    <form method="POST" action="{{ route('admin.reward-distributions.store') }}"
        class="bg-white p-6 rounded-xl shadow space-y-4">
        @csrf

        <div>
            <label class="text-sm font-semibold">報酬</label>
            <select name="reward_id" class="w-full border rounded-lg p-2">
                @foreach($rewards as $reward)
                <option value="{{ $reward->id }}">
                    {{ $reward->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">数量（空欄＝無制限）</label>
            <input type="number" name="quantity" class="w-full border rounded-lg p-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold">配布開始（ガチャ投入）</label>
                <input type="datetime-local" name="starts_at" class="border rounded-lg p-2">
            </div>

            <div>
                <label class="text-sm font-semibold">配布終了（ガチャ除外）</label>
                <input type="datetime-local" name="ends_at" class="border rounded-lg p-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold">報酬の有効期限（取得後）</label>
            <input type="datetime-local" name="reward_expires_at"
                class="w-full border rounded-lg p-2">
        </div>

        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
            配布候補に追加
        </button>
    </form>

    {{-- 一覧 --}}
    <div class="bg-white rounded-xl shadow divide-y">
        @foreach($distributions as $d)
        <div class="p-4 flex justify-between items-center">
            <div>
                <p class="font-semibold">{{ $d->reward->name }}</p>

                <p class="text-sm text-gray-500">
                    数量：{{ $d->quantity ?? '無制限' }}
                </p>

                {{-- 配布期間 --}}
                @if($d->starts_at || $d->ends_at)
                <p class="text-xs text-gray-400 mt-1">
                    配布期間：
                    {{ $d->starts_at?->format('Y/m/d H:i') ?? '－' }}
                    〜
                    {{ $d->ends_at?->format('Y/m/d H:i') ?? '－' }}
                </p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                {{-- 配布ON/OFF --}}
                <form method="POST"
                    action="{{ route('admin.reward-distributions.toggle', $d) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        class="px-4 py-1 rounded-lg text-white
                            {{ $d->is_active ? 'bg-green-600' : 'bg-gray-400' }}">
                        {{ $d->is_active ? '配布中' : '停止中' }}
                    </button>
                </form>

                {{-- 削除 --}}
                <form method="POST"
                    action="{{ route('admin.reward-distributions.destroy', $d) }}"
                    onsubmit="return confirm('この配布候補を削除しますか？');">
                    @csrf
                    @method('DELETE')

                    <button class="px-3 py-1 text-sm bg-red-500 text-white rounded-lg">
                        削除
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection