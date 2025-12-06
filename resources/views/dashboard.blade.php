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
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $thisMonthGoals }}</div>
                        <p class="text-gray-500 text-sm">件</p>
                    </div>
                </div>
            </div>

            <!-- 新しい活動を記録ボタン -->
            <div class="mb-6">
                <a href="/goals/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                  ➕ 新しい活動を記録
                </a>
            </div>

            <!-- 最近の活動 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">📝 最近の記録</h3>
        
                @if($recentGoals->count() > 0)
                    <div class="space-y-4">
                         @foreach($recentGoals as $goal)
                             <!-- 目標カード -->
                             <div class="border-l-4 border-indigo-500 bg-gray-50 p-4 rounded-r-lg hover:shadow-md transition">
                                 <div class="flex justify-between items-start">
                                     <div>
                                         <div class="font-semibold text-gray-800">{{ $goal->category }}</div>
                                         <div class="text-gray-600 mt-1">{{ $goal->title }}</div>
                                         <div class="text-gray-500 text-sm mt-1">{{ $goal->deadline }}</div>
                                     </div>
                                     <div>
                                         <a href="/goals/{{ $goal->id }}/edit" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                                             編集
                                         </a>
                                     </div>
                                 </div>
                             </div>
                         @endforeach
                     </div>

                     <div class="mt-6 text-center">
                     </div>
                 @else
                     <div class="text-center py-8 text-gray-500">
                         <p class="mb-4">まだ目標が登録されていません</p>
                         <a href="/goals/create" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                             最初の目標を作成
                         </a>
                     </div>
                 @endif
             </div>
         </div>

                    <div class="mt-6 text-center">
                        <a href="/activities" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                            すべての活動を見る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
