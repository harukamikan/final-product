@extends('layouts.admin')

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
                            
                            {{-- 半期目標（定性的） --}}
                            @if(isset($userData['semester_goal']) && $userData['semester_goal'])
                                <div class="mb-4 p-3 bg-blue-50 rounded">
                                    <div class="text-sm text-blue-600 font-semibold">📋 半期目標</div>
                                    <div class="text-gray-800">{{ $userData['semester_goal'] }}</div>
                                </div>
                            @endif

                            {{-- ミッション（定量的） --}}
                            @if(isset($userData['missions']) && is_array($userData['missions']) && count($userData['missions']) > 0)
                                <div class="space-y-2">
                                    <div class="text-sm text-green-600 font-semibold">🎯 個人ミッション</div>
                                    @foreach($userData['missions'] as $mission)
                                        <div class="pl-4 border-l-2 border-green-500">
                                            <div class="font-semibold">{{ $mission['category'] ?? 'カテゴリなし' }}</div>
                                            <div class="text-gray-800">{{ $mission['title'] ?? '内容なし' }} ({{ $mission['count'] ?? 1 }}回)</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- 何も抽出されなかった場合 --}}
                            @if(
                                (!isset($userData['semester_goal']) || !$userData['semester_goal']) && 
                                (!isset($userData['missions']) || count($userData['missions']) === 0)
                            )
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