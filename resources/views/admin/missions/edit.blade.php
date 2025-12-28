{{-- resources/views/admin/missions/edit.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">ミッション編集</h1>
        <p class="text-sm text-gray-700 mt-1">
            {{ $mission->title }} の設定を更新します。
        </p>
    </div>

    <form method="POST" action="{{ route('admin.missions.update', $mission) }}">
        @method('PUT')
        @include('admin.missions._form')
    </form>
</div>
@endsection
