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
            <div class="overflow-hidden shadow-2xl card-shadow sm:rounded-lg mb-6" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-white mb-4">📈 月別マイル獲得</h3>
                    <div style="height: 400px; overflow-x: auto;">
                        <div style="min-width: 800px; height: 100%;">
                    <canvas id="monthlyChart"></canvas>
               </div>
           </div>
       </div>
   </div>

            <!-- 累積マイル推移グラフ -->
            <div class="overflow-hidden shadow-2xl card-shadow sm:rounded-lg mb-6" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-white mb-4">📊 累積マイル推移</h3>
                    <div style="height: 400px; overflow-x: auto;">
                        <div style="min-width: 800px; height: 100%;">
                            <canvas id="cumulativeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden shadow-2xl card-shadow sm:rounded-lg" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-white mb-4">🎯 カテゴリ別マイル獲得</h3>
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
                labels: monthlyData.map(d => {
                    const [year, month] = d.month.split('-');
                    return `${year}年${parseInt(month)}月`;
                }),
                datasets: [{
                    label: 'マイル獲得数',
                    data: monthlyData.map(d => parseInt(d.total)),
                    borderColor: 'rgb(255, 255, 255)',
                    backgroundColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgb(255, 255, 255)',
                    pointBorderColor: 'rgba(99, 102, 241, 0.8)',
                    pointBorderWidth: 3,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                       grid: {
                           color: 'rgba(255, 255, 255, 0.1)',
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.9)',
                        }
                    },
                    y: {
                       grid: {
                           color: 'rgba(255, 255, 255, 0.1)',
                           drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.9)',
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // 累積マイルグラフ
        const cumulativeData = @json($cumulativeMiles);
        new Chart(document.getElementById('cumulativeChart'), {
            type: 'line',
            data: {
                labels: cumulativeData.map(d => {
                    const [year, month] = d.month.split('-');
                    return `${year}年${parseInt(month)}月`;
                }),
                datasets: [{
                    label: '累積マイル',
                    data: cumulativeData.map(d => parseInt(d.total)),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.9)',
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.9)',
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // カテゴリ別マイルグラフ
        const categoryData = @json($categoryMiles);
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: categoryData.map(d => d.type || '未分類'),
                datasets: [{
                    label: 'マイル獲得数',
                    data: categoryData.map(d => parseInt(d.total)),
                    backgroundColor: [
                         'rgba(79, 70, 229, 0.8)',
                         'rgba(245, 158, 11, 0.8)',
                         'rgba(34, 197, 94, 0.8)',
                         'rgba(239, 68, 68, 0.8)',
                         'rgba(236, 72, 153, 0.8)'
                        ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                         labels: {
                              color: 'rgba(255, 255, 255, 0.9)',
                              font: {
                                  size: 12,
                                  weight: '500'
                            }
                       }
                    }
                }
            }
        });   
    </script>
@endsection
