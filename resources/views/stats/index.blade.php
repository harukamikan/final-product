@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 text-amber-100">📊 統計</h2>

            <!-- 統計サマリー -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">総マイル</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $totalMiles }}</div>
                        <p class="text-gray-500 text-sm">累計獲得マイル</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-gray-600 text-sm font-semibold uppercase mb-2">総活動数</h3>
                        <div class="text-5xl font-bold text-indigo-600 mb-2">{{ $totalActivities }}</div>
                        <p class="text-gray-500 text-sm">累計活動回数</p>
                    </div>
                </div>
            </div>

            <!-- グラフエリア -->
            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">📈 月別マイル獲得</h3>
                    
                    <div style="height: 400px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">🎯 カテゴリ別マイル獲得</h3>
                    <div style="height: 400px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 月別マイルグラフ
        const monthlyData = @json($monthlyMiles);
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'マイル獲得数',
                    data: monthlyData.map(d => parseInt(d.total)),
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // カテゴリ別マイルグラフ
        const categoryData = @json($categoryMiles);
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryData.map(d => d.type || '未分類'),
                datasets: [{
                    label: 'マイル獲得数',
                    data: categoryData.map(d => parseInt(d.total)),
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    </script>
@endsection
