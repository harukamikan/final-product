@extends('layouts.admin')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">管理者設定</h2>
                    
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.store') }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                通知方法
                            </label>
                            
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="notification_type" value="slack"
                                        {{ (!$setting || $setting->notification_type === 'slack') ? 'checked' : '' }}
                                        class="mr-2">
                                    Slack
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="radio" name="notification_type" value="email"
                                        {{ ($setting && $setting->notification_type === 'email') ? 'checked' : '' }}
                                        class="mr-2">
                                    メール
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="slack_id" class="block text-sm font-medium text-gray-700">
                                Slack User ID
                            </label>
                            <input type="text" name="slack_id" id="slack_id"
                                value="{{ old('slack_id', optional($setting)->slack_id) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <p class="mt-1 text-sm text-gray-500">
                                Slack ID（例: U01234ABCDE）
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                メールアドレス
                            </label>
                            <input type="email" name="email" id="email"
                                value="{{ old('email', optional($setting)->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            保存
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection