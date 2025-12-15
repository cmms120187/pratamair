@extends('layouts.app')
@section('content')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .filter-item {
        animation: slideInRight 0.4s ease-out forwards;
        opacity: 0;
    }
    .filter-item:nth-child(1) { animation-delay: 0.1s; }
    .filter-item:nth-child(2) { animation-delay: 0.15s; }
    .filter-item:nth-child(3) { animation-delay: 0.2s; }
    .filter-item:nth-child(4) { animation-delay: 0.25s; }
    .stat-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    .stat-card:hover::before {
        left: 100%;
    }
    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="w-full mx-auto">
        <!-- Header - Compact Single Row -->
        <div class="mb-6 animate-fade-in-up">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Title and Subtitle -->
                <div class="flex-1 min-w-[300px]">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                        Root Cause Analysis
                    </h1>
                    <p class="text-sm text-gray-600">
                        Analisis akar penyebab downtime berdasarkan frekuensi dan durasi
                    </p>
                </div>
                
                <!-- Filters and Period Info - All in one row -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Period Info -->
                    @php
                        $periodText = '';
                        if ($selectedMonth != 'all' && $selectedYear != 'all') {
                            $periodText = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');
                        } elseif ($selectedYear != 'all') {
                            $periodText = 'All Months ' . $selectedYear;
                        } elseif ($selectedMonth != 'all') {
                            $periodText = \Carbon\Carbon::create()->month($selectedMonth)->format('F') . ' (All Years)';
                        } else {
                            $periodText = 'All Period';
                        }
                    @endphp
                    <div class="text-sm bg-gradient-to-r from-blue-100 to-purple-100 px-3 py-1.5 rounded-lg border-2 border-blue-400 whitespace-nowrap shadow-md filter-item">
                        <span class="font-bold text-blue-800">Period:</span> 
                        <span class="font-semibold text-gray-900">{{ $periodText }}</span>
                    </div>
                    
                    <!-- Filters -->
                    <form method="GET" action="{{ route('root-cause-analysis.index') }}" class="flex flex-wrap items-center gap-2">
                        <!-- Month Filter -->
                        <div class="relative filter-item">
                            <select name="month" id="month" 
                                    onchange="this.form.submit();"
                                    class="appearance-none bg-white border-2 border-indigo-500 text-indigo-700 rounded-lg px-4 py-1.5 text-sm font-semibold shadow-md hover:shadow-lg hover:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer transition-all transform hover:scale-105 pr-8 min-w-[140px]">
                                <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>📅 Semua Bulan</option>
                                @foreach($months as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Year Filter -->
                        <div class="relative filter-item">
                            <select name="year" id="year" 
                                    onchange="this.form.submit();"
                                    class="appearance-none bg-white border-2 border-blue-500 text-blue-700 rounded-lg px-4 py-1.5 text-sm font-semibold shadow-md hover:shadow-lg hover:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer transition-all transform hover:scale-105 pr-8 min-w-[120px]">
                                <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>📆 Semua Tahun</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Data Source Filter -->
                        <div class="relative filter-item">
                            <select name="data_source" id="data_source" 
                                    onchange="this.form.submit();"
                                    class="appearance-none bg-white border-2 border-green-500 text-green-700 rounded-lg px-4 py-1.5 text-sm font-semibold shadow-md hover:shadow-lg hover:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 cursor-pointer transition-all transform hover:scale-105 pr-8 min-w-[160px]">
                                <option value="downtime" {{ $dataSource == 'downtime' ? 'selected' : '' }}>💾 Downtime</option>
                                <option value="downtime_erp" {{ $dataSource == 'downtime_erp' ? 'selected' : '' }}>💾 Downtime ERP</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="stat-card bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-xl p-6 text-white animate-fade-in-up" style="animation-delay: 0.1s; opacity: 0;">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-white/90 mb-1">Total Root Cause</div>
                <div class="text-3xl font-bold text-white">{{ $stats['total_root_causes'] }}</div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-xl p-6 text-white animate-fade-in-up" style="animation-delay: 0.2s; opacity: 0;">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-white/90 mb-1">Total Frekuensi</div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['total_frequency']) }}</div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-xl p-6 text-white animate-fade-in-up" style="animation-delay: 0.3s; opacity: 0;">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-white/90 mb-1">Total Durasi</div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['total_duration'], 2) }}</div>
                <div class="text-xs text-white/80 mt-1">menit</div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl shadow-xl p-6 text-white animate-fade-in-up" style="animation-delay: 0.4s; opacity: 0;">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-white/90 mb-1">Rata-rata Frekuensi</div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['avg_frequency_per_cause'], 2) }}</div>
            </div>
            
            <div class="stat-card bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl shadow-xl p-6 text-white animate-fade-in-up" style="animation-delay: 0.5s; opacity: 0;">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-white/90 mb-1">Rata-rata Durasi</div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['avg_duration_per_incident'], 2) }}</div>
                <div class="text-xs text-white/80 mt-1">menit</div>
            </div>
        </div>
        
        <!-- Pareto Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 animate-fade-in-up" style="animation-delay: 0.3s; opacity: 0;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Chart Root Cause Analysis
                </h2>
                <div class="text-sm text-gray-600 bg-blue-50 px-4 py-2 rounded-full font-semibold">
                    Top 10 Root Causes
                </div>
            </div>
            <div class="relative" style="height: 500px;">
                <canvas id="rootCauseChart"></canvas>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s; opacity: 0;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Data Root Cause Analysis
                </h2>
                <div class="text-sm text-gray-600 bg-blue-50 px-4 py-2 rounded-full font-semibold">
                    Menampilkan {{ count($analysisData) }} root cause
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Root Cause</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">% Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Durasi (min)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">% Durasi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Rata-rata Durasi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Kumulatif %</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($analysisData as $data)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition-all duration-200 hover:shadow-sm animate-fade-in-up" style="animation-delay: {{ $loop->index * 0.03 }}s; opacity: 0;">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">
                                <span class="px-3 py-1.5 rounded-full font-bold {{ $data['rank'] <= 3 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : ($data['rank'] <= 10 ? 'bg-gradient-to-r from-blue-400 to-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-700') }}">
                                    #{{ $data['rank'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                <div class="max-w-md truncate" title="{{ $data['root_cause'] }}">
                                    {{ $data['root_cause'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-blue-600">
                                {{ number_format($data['frequency']) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-purple-600">
                                {{ number_format($data['frequency_percentage'], 2) }}%
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                {{ number_format($data['duration'], 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-indigo-600">
                                {{ number_format($data['duration_percentage'], 2) }}%
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-700">
                                {{ number_format($data['avg_duration'], 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                <span class="px-3 py-1.5 rounded-full font-bold
                                    @if($data['cumulative_percentage'] <= 80) bg-gradient-to-r from-green-400 to-green-600 text-white shadow-md
                                    @elseif($data['cumulative_percentage'] <= 95) bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md
                                    @else bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg
                                    @endif">
                                    {{ number_format($data['cumulative_percentage'], 2) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-semibold text-gray-400">Tidak ada data root cause untuk periode yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('rootCauseChart');
    if (ctx) {
        const analysisData = @json($analysisData);
        
        if (analysisData.length > 0) {
            // Get top 10 for chart
            const topData = analysisData.slice(0, 10);
            const labels = topData.map(d => {
                const cause = d.root_cause;
                return cause.length > 20 ? cause.substring(0, 20) + '...' : cause;
            });
            const frequencies = topData.map(d => d.frequency);
            const cumulativePercentages = topData.map(d => d.cumulative_percentage);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Frekuensi',
                            data: frequencies,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Kumulatif %',
                            data: cumulativePercentages,
                            type: 'line',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            yAxisID: 'y1',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Frekuensi'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Kumulatif %'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            max: 100
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endsection

