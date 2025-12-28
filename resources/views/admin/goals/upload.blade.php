@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">📊 半期目標一括アップロード</h2>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
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

                <div class="mb-6">
                    <h3 class="font-semibold mb-2">📝 Excelフォーマット</h3>
                    <p class="text-gray-600 mb-2">以下の形式でExcelファイルを作成してください:</p>
                    <table class="border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2">名前</th>
                                <th class="border border-gray-300 px-4 py-2">目標カテゴリ</th>
                                <th class="border border-gray-300 px-4 py-2">目標内容</th>
                                <th class="border border-gray-300 px-4 py-2">期限</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">はるか</td>
                                <td class="border border-gray-300 px-4 py-2">ブログ</td>
                                <td class="border border-gray-300 px-4 py-2">技術記事を5本書く</td>
                                <td class="border border-gray-300 px-4 py-2">2025-06-30</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('admin.goals.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Excelファイルを選択
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>

                    <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        一括登録
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection