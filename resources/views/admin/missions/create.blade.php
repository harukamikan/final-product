{{-- resources/views/admin/missions/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ===== ヘッダ ===== --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                ミッション作成
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                新しいミッションの条件や報酬を設定します。
            </p>
        </div>

        {{-- ===== フォーム ===== --}}
        <form method="POST"
              action="{{ route('admin.missions.store') }}"
              class="space-y-6">
            @csrf

            {{-- 入力カード --}}
            <div class="bg-white rounded-2xl shadow-md p-6 space-y-6">
                @include('admin.missions._form')
            </div>

            {{-- ===== 操作ボタン（スマホ最適） ===== --}}
            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">

                {{-- 戻る --}}
                <a href="{{ route('admin.missions.index') }}"
                   class="w-full sm:w-auto text-center
                          px-4 py-2 rounded-lg border
                          text-slate-600 hover:bg-slate-50">
                    戻る
                </a>

                {{-- 保存 --}}
                <button type="submit"
                        class="w-full sm:w-auto
                               px-4 py-2 rounded-lg
                               bg-indigo-600 text-white
                               font-semibold hover:bg-indigo-700 transition">
                    作成する
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
