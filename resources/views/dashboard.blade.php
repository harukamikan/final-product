@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6">ようこそ、{{ auth()->user()->name }}さん！</h2>

            <!-- マイル統計 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">総マイル</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">150</div>
                        <p class="text-gray-500 text-sm">累計獲得マイル</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">現在のランク</h3>
                        <div class="text-3xl font-bold text-yellow-500 mb-2">🥈 シルバー</div>
                        <p class="text-gray-500 text-sm">次のランクまで あと50マイル</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">今月の活動</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">3</div>
                        <p class="text-gray-500 text-sm">件</p>
                    </div>
                </div>
            </div>

            <!-- 新しい活動を記録ボタン -->
            <div class="mb-6">
                <a href="/activities/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    ➕ 新しい活動を記録
                </a>
            </div>

            <!-- 最近の活動 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">📝 最近の活動</h3>
                    
                    <div class="space-y-4">
                        <!-- 活動カード1 -->
                        <div class="border-l-4 border-green-500 bg-gray-50 p-4 rounded-r-lg hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-gray-800">📝 ブログ投稿</div>
                                    <div class="text-gray-600 mt-1">Laravelでの目標管理アプリ開発記</div>
                                    <div class="text-gray-500 text-sm mt-1">2024年12月1日</div>
                                </div>
                                <div class="text-indigo-600 font-bold text-lg">+10マイル</div>
                            </div>
                        </div>

                        <!-- 活動カード2 -->
                        <div class="border-l-4 border-orange-500 bg-gray-50 p-4 rounded-r-lg hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-gray-800">🎤 登壇</div>
                                    <div class="text-gray-600 mt-1">社内勉強会「チーム開発のコツ」</div>
                                    <div class="text-gray-500 text-sm mt-1">2024年11月28日</div>
                                </div>
                                <div class="text-indigo-600 font-bold text-lg">+50マイル</div>
                            </div>
                        </div>

                        <!-- 活動カード3 -->
                        <div class="border-l-4 border-blue-500 bg-gray-50 p-4 rounded-r-lg hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-gray-800">📜 資格取得</div>
                                    <div class="text-gray-600 mt-1">AWS認定ソリューションアーキテクト</div>
                                    <div class="text-gray-500 text-sm mt-1">2024年11月15日</div>
                                </div>
                                <div class="text-indigo-600 font-bold text-lg">+100マイル</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="/activities" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                            すべての活動を見る →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
