@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- ===== ページタイトル ===== --}}
        <h2 class="text-2xl font-bold text-slate-800">
            📊 統計
        </h2>

        {{-- ===== 統計サマリー ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 uppercase mb-2">
                    総マイル
                </p>
                <p class="text-4xl font-bold text-indigo-600 mb-1">
                    {{ $totalMiles }}
                </p>
                <p class="text-sm text-slate-500">
                    累計獲得マイル
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-sm font-semibold text-slate-500 uppercase mb-2">
                    総活動数
                </p>
                <p class="text-4xl font-bold text-indigo-600 mb-1">
                    {{ $totalActivities }}
                </p>
                <p class="text-sm text-slate-500">
                    累計活動回数
                </p>
            </div>
        </div>

        {{-- ===== グラフエリア（3列レイアウト） ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 月別マイル獲得 --}}
            <div class="bg-white rounded-2xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    📈 月別マイル獲得
                </h3>
                <div class="h-[300px]">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            {{-- 累積マイル推移 --}}
            <div class="bg-white rounded-2xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    📊 累積マイル推移
                </h3>
                <div class="h-[300px]">
                    <canvas id="cumulativeChart"></canvas>
                </div>
            </div>

            {{-- カテゴリ別マイル --}}
            <div class="bg-white rounded-2xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    🎯 カテゴリ別マイル獲得
                </h3>
                <div class="h-[300px]">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ===== Chart.js ===== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 月別マイル
    const monthlyData = @json($monthlyMiles);
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => {
                const [year, month] = d.month.split('-');
                return `${year}年${parseInt(month)}月`;
            }),
            datasets: [{
                data: monthlyData.map(d => parseInt(d.total)),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(99, 102, 241)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    // 累積マイル
    const cumulativeData = @json($cumulativeMiles);
    new Chart(document.getElementById('cumulativeChart'), {
        type: 'line',
        data: {
            labels: cumulativeData.map(d => {
                const [year, month] = d.month.split('-');
                return `${year}年${parseInt(month)}月`;
            }),
            datasets: [{
                data: cumulativeData.map(d => parseInt(d.total)),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(34, 197, 94)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    // カテゴリ別
    const categoryData = @json($categoryMiles);
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: categoryData.map(d => d.category),
            datasets: [{
                data: categoryData.map(d => parseInt(d.total)),
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
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
                        color: '#334155',
                        font: { size: 12 }
                    }
                }
            }
        }
    });
</script>
@endsection
