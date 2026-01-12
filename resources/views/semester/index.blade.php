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
        </div>
    </div>

    <style>
        .card-shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
@endsection
