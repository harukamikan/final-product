{{-- resources/views/admin/missions/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">ミッション作成</h1>
        <p class="text-sm text-gray-500 mt-1">
            新しいミッションの条件や報酬を設定します。
        </p>
    </div>

    <form method="POST" action="{{ route('admin.missions.store') }}">
        @include('admin.missions._form')
    </form>
</div>
@endsection
