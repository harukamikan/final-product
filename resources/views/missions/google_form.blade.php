{{-- resources/views/missions/report-google-form.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 space-y-6">

    {{-- タイトル --}}
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $mission->title }} の報告
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            このミッションに対応する GoogleフォームのURLを入力して送信してください。
        </p>
    </div>

    {{-- フォーム --}}
    <form method="POST" action="{{ route('missions.google_form.store') }}" class="space-y-4">
        @csrf

        {{-- mission_key は hidden --}}
        <input type="hidden" name="mission_key" value="{{ $mission->key }}">

        <div>
            <label class="block text-sm font-medium text-gray-700">
                GoogleフォームのURL
            </label>
            <input type="url" name="url"
                   value="{{ old('url') }}"
                   placeholder="https://docs.google.com/forms/..."
                   class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('url')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('missions.index') }}"
               class="text-xs text-gray-500 hover:underline">
                ミッション一覧に戻る
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                報告を送信する
            </button>
        </div>
    </form>

</div>
@endsection
