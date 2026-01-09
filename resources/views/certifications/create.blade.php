@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">資格取得を登録</h1>
        <p class="text-sm text-gray-500 mt-2">
            取得した資格の情報を入力してください
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="{{ route('certifications.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- 資格名 --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    資格名 <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="例: AWS 認定ソリューションアーキテクト - アソシエイト"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                />
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 主催団体 --}}
            <div>
                <label for="organization" class="block text-sm font-semibold text-gray-700 mb-2">
                    主催団体
                </label>
                <input
                    type="text"
                    id="organization"
                    name="organization"
                    value="{{ old('organization') }}"
                    placeholder="例: Amazon Web Services"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('organization') border-red-500 @enderror"
                />
                @error('organization')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 取得日 --}}
            <div>
                <label for="acquired_at" class="block text-sm font-semibold text-gray-700 mb-2">
                    取得日 <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="acquired_at"
                    name="acquired_at"
                    value="{{ old('acquired_at') }}"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('acquired_at') border-red-500 @enderror"
                />
                @error('acquired_at')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- スコア --}}
                <div>
                    <label for="score" class="block text-sm font-semibold text-gray-700 mb-2">
                        スコア
                    </label>
                    <input
                        type="text"
                        id="score"
                        name="score"
                        value="{{ old('score') }}"
                        placeholder="例: 850/1000"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('score') border-red-500 @enderror"
                    />
                    @error('score')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 難易度 --}}
                <div>
                    <label for="difficulty" class="block text-sm font-semibold text-gray-700 mb-2">
                        難易度
                    </label>
                    <select
                        id="difficulty"
                        name="difficulty"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('difficulty') border-red-500 @enderror"
                    >
                        <option value="">選択してください</option>
                        <option value="初級" {{ old('difficulty') === '初級' ? 'selected' : '' }}>初級</option>
                        <option value="中級" {{ old('difficulty') === '中級' ? 'selected' : '' }}>中級</option>
                        <option value="上級" {{ old('difficulty') === '上級' ? 'selected' : '' }}>上級</option>
                    </select>
                    @error('difficulty')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- メモ --}}
            <div>
                <label for="memo" class="block text-sm font-semibold text-gray-700 mb-2">
                    メモ
                </label>
                <textarea
                    id="memo"
                    name="memo"
                    rows="4"
                    placeholder="取得のきっかけ、勉強方法、感想など自由に入力してください"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('memo') border-red-500 @enderror"
                >{{ old('memo') }}</textarea>
                @error('memo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 送信ボタン --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('timeline.index', ['type' => 'certification']) }}"
                   class="text-gray-600 hover:text-gray-900 font-medium text-sm transition">
                    ← タイムラインに戻る
                </a>
                <button
                    type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    登録する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
