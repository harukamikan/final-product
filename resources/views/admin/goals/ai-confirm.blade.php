@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">✅ 抽出結果の確認</h2>

                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500">
                    <p class="text-yellow-700">
                        以下の内容で登録します。間違いがあれば「戻る」ボタンで修正してください。
                    </p>
                </div>

                <form action="{{ route('admin.goals.ai.store') }}" method="POST">
                    @csrf

                    @foreach($extractedData as $index => $userData)
                        <div class="mb-6 p-4 border rounded">
                            <h3 class="font-semibold text-lg mb-3">👤 {{ $userData['name'] ?? '名前不明' }}</h3>
                            
                            @if(isset($userData['goals']) && count($userData['goals']) > 0)
                                <div class="space-y-3">
                                    @foreach($userData['goals'] as $goalIndex => $goal)
                                        <div class="pl-4 border-l-2 border-indigo-500">
                                            <div class="text-sm text-gray-600">目標 {{ $goalIndex + 1 }}</div>
                                            <div class="font-semibold">{{ $goal['category'] ?? 'カテゴリなし' }}</div>
                                            <div class="text-gray-800">{{ $goal['title'] ?? '内容なし' }}</div>
                                            @if(isset($goal['deadline']))
                                                <div class="text-sm text-gray-600">期限: {{ $goal['deadline'] }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">目標が抽出されませんでした</p>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex gap-4">
                        <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            この内容で登録
                        </button>
                        <a href="{{ route('admin.goals.ai.index') }}" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            戻る
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection