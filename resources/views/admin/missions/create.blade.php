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
            <p class="text-sm text-slate-700 mt-1">
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
        </form>

    </div>
</div>
@endsection
