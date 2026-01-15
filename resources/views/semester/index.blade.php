@extends('layouts.admin')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold mb-6 text-amber-100">📅 半期設定</h2>

            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.semester.update') }}">
                        @csrf

                        <!-- 半期開始日 -->
                        <div class="mb-4">
                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                半期開始日
                            </label>
                            <input 
                                type="date" 
                                name="start_date" 
                                id="start_date"
                                value="{{ old('start_date', $setting->start_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 半期終了日 -->
                        <div class="mb-4">
                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                半期終了日
                            </label>
                            <input 
                                type="date" 
                                name="end_date" 
                                id="end_date"
                                value="{{ old('end_date', $setting->end_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 自動リセット -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="auto_reset_enabled" 
                                    value="1"
                                    {{ $setting->auto_reset_enabled ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">
                                    終了日に自動的にリセットする
                                </span>
                            </label>
                        </div>

                        <!-- 保存ボタン -->
                        <div class="flex justify-end">
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                保存
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- 今すぐリセットボタン -->
            <div class="mt-6 bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🔄 手動リセット</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        現在の半期を終了し、新しい半期を開始します。全ユーザーのマイルとランクがリセットされます。
                    </p>
                    <form method="POST" action="{{ route('admin.semester.reset') }}" onsubmit="return confirm('本当にリセットしますか？この操作は取り消せません。');">
                        @csrf
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            今すぐリセット
                        </button>
                    </form>
                </div>
            </div>

            <!-- 半期一覧 -->
            <div class="mt-6 bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">📋 半期一覧</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">ID</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">期間</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">状態</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($semesters as $semester)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $semester->id }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ $semester->start_date->format('Y/m/d') }} 〜 {{ $semester->end_date->format('Y/m/d') }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($semester->id === $setting->id)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">現在</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">過去</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($semester->id !== $setting->id)
                                            <form method="POST" action="{{ route('admin.semester.destroy', $semester) }}" class="inline" onsubmit="return confirm('この半期を削除しますか？関連するマイル履歴も削除されます。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                    削除
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <style>
        .card-shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
@endsection
