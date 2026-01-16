@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">🤖 AI自動抽出アップロード</h2>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('errors') && count(session('errors')) > 0)
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <p class="font-bold">以下のユーザーが見つかりませんでした:</p>
                        <ul class="list-disc list-inside">
                            @foreach(session('errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 p-4 bg-blue-50 rounded">
                    <h3 class="font-semibold mb-2">✨ 特徴</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        <li>自由な形式でOK（テンプレート不要）</li>
                        <li>テキストファイル、Excel、Word対応</li>
                        <li>AIが自動で名前・目標・期限を抽出</li>
                        <li>確認画面で修正可能</li>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3 class="font-semibold mb-2">📝 入力例</h3>
                    <div class="bg-gray-100 p-4 rounded text-sm">
                        <pre>はるか
・今期はブログを頑張りたい！技術記事5本目指す
・期限は6月末まで
・あと資格も取りたいな。一陸技を1月20日までに

田中太郎
目標：社内LTで登壇する
いつまで：3月31日</pre>
                    </div>
                </div>

                <form action="{{ route('admin.goals.ai.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ファイルを選択（.txt, .xlsx, .xls, .docx）
                        </label>
                        <input type="file" name="files[]" accept=".txt,.xlsx,.xls,.docx" required multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">※ 複数ファイルを選択できます</p>
                        </div>

                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        AI抽出を開始
                    </button>
                </form>

                
            </div>
        </div>
    </div>
</div>
@endsection