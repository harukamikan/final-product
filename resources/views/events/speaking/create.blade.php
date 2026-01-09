@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">イベント登壇を登録</h1>
        <p class="text-sm text-gray-500 mt-2">
            登壇したイベントの connpass URL を入力してください
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="{{ route('events.speaking.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- イベントURL --}}
            <div>
                <label for="event_url" class="block text-sm font-semibold text-gray-700 mb-2">
                    イベントURL <span class="text-red-500">*</span>
                </label>
                <input
                    type="url"
                    id="event_url"
                    name="event_url"
                    value="{{ old('event_url') }}"
                    required
                    placeholder="https://connpass.com/event/12345/"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('event_url') border-red-500 @enderror"
                />
                @error('event_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    connpass のイベントURLを入力してください。APIでイベント情報を自動取得します。
                </p>
            </div>

            {{-- API情報 --}}
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-purple-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-purple-800">connpass API について</p>
                        <p class="text-xs text-purple-700 mt-1">
                            URL入力後、connpass API を使ってイベント情報（タイトル、開催日時、参加者数等）を自動取得します。<br>
                            取得に失敗した場合はURLのみ保存されます。
                        </p>
                    </div>
                </div>
            </div>

            {{-- 送信ボタン --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('timeline.index', ['type' => 'event_speaking']) }}"
                   class="text-gray-600 hover:text-gray-900 font-medium text-sm transition">
                    ← タイムラインに戻る
                </a>
                <button
                    type="submit"
                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    登録する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
