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
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
    }
    .animate-slide-in-left {
        animation: slideInLeft 0.6s ease-out forwards;
    }
    .animate-pulse-slow {
        animation: pulse 3s ease-in-out infinite;
    }
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
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
    .stat-card-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-card-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .chart-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        border-color: #667eea;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
        transform: translateY(-5px);
    }
    .info-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .downtime-item {
        transition: all 0.3s ease;
        background: linear-gradient(90deg, #fff 0%, #f8f9fa 100%);
    }
    .downtime-item:hover {
        transform: translateX(10px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background: linear-gradient(90deg, #fff 0%, #e9ecef 100%);
    }
    .delay-100 { animation-delay: 0.1s; opacity: 0; }
    .delay-200 { animation-delay: 0.2s; opacity: 0; }
    .delay-300 { animation-delay: 0.3s; opacity: 0; }
    .delay-400 { animation-delay: 0.4s; opacity: 0; }
    .delay-500 { animation-delay: 0.5s; opacity: 0; }
    .delay-600 { animation-delay: 0.6s; opacity: 0; }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="w-full mx-auto">
        <!-- Header -->
        <div class="mb-6 sm:mb-8 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                Dashboard
            </h1>
            <p class="text-base sm:text-lg lg:text-xl text-gray-600 font-medium">
                {{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}
            </p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <!-- Filter Bulan dan Tahun -->
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2" id="filterForm">
                        <input type="hidden" name="data_source" value="{{ $dataSource }}">
                        <label for="month" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Bulan:</label>
                        <select name="month" id="month" 
                                onchange="document.getElementById('filterForm').submit();"
                                class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $filterMonth == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        <label for="year" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Tahun:</label>
                        <select name="year" id="year" 
                                onchange="document.getElementById('filterForm').submit();"
                                class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                    <!-- Data Source -->
                    <form method="GET" action="{{ route('dashboard') }}" class="inline-block" id="dataSourceForm">
                        <input type="hidden" name="month" value="{{ $filterMonth }}">
                        <input type="hidden" name="year" value="{{ $filterYear }}">
                        <label for="data_source" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Data Source:</label>
                        <select name="data_source" id="data_source" 
                                onchange="document.getElementById('dataSourceForm').submit();"
                                class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="downtime_erp2" {{ $dataSource === 'downtime_erp2' ? 'selected' : '' }}>Downtime ERP2</option>
                            <option value="downtime_erp" {{ $dataSource === 'downtime_erp' ? 'selected' : '' }}>Downtime ERP</option>
                            <option value="downtime" {{ $dataSource === 'downtime' ? 'selected' : '' }}>Downtime</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Top Statistics Cards - This Month -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Downtime Count -->
            <div class="stat-card stat-card-1 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-100 hover:shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full animate-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-xs sm:text-sm font-medium text-white/80 mb-1">Total Breakdowns</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-2 animate-pulse-slow">{{ number_format($monthDowntimeCount) }}</p>
                    <p class="text-xs text-white/70 mt-2">{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</p>
                </div>
            </div>
            
            <!-- Total Duration -->
            <div class="stat-card stat-card-2 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-200 hover:shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full animate-float" style="animation-delay: 0.2s;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-xs sm:text-sm font-medium text-white/80 mb-1">Total Duration</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-2 animate-pulse-slow">{{ number_format($monthDowntime, 0) }}</p>
                    <p class="text-xs text-white/70 mt-2">minutes</p>
                </div>
            </div>

            <!-- Average Duration -->
            <div class="stat-card stat-card-3 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300 hover:shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full animate-float" style="animation-delay: 0.4s;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white/80 mb-1">Avg per Breakdown</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-2 animate-pulse-slow">{{ number_format($avgDowntimeDuration, 1) }}</p>
                    <p class="text-xs text-white/70 mt-2">minutes</p>
                </div>
            </div>

            <!-- Average per Day -->
            <div class="stat-card stat-card-4 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-400 hover:shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full animate-float" style="animation-delay: 0.6s;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white/80 mb-1">Avg per Day</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-2 animate-pulse-slow">{{ number_format($avgDowntimePerDay, 1) }}</p>
                    <p class="text-xs text-white/70 mt-2">minutes/day</p>
                </div>
            </div>

            <!-- Most Problematic Machine -->
            <div class="stat-card stat-card-5 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-500 hover:shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full animate-float" style="animation-delay: 0.8s;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white/80 mb-1">Top Machine</p>
                    @if($mostProblematicMachine)
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold mt-2 animate-pulse-slow" title="{{ $mostProblematicMachine->idMachine }}">{{ strlen($mostProblematicMachine->idMachine) > 12 ? substr($mostProblematicMachine->idMachine, 0, 12) . '...' : $mostProblematicMachine->idMachine }}</p>
                        <p class="text-xs text-white/70 mt-2">{{ number_format((float)($mostProblematicMachine->total_duration ?? 0), 0) }} min</p>
                    @else
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold mt-2">-</p>
                        <p class="text-xs text-white/70 mt-2">No data</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chart Grid - Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Top 10 Machine Downtime -->
            <div class="chart-card rounded-xl shadow-lg p-6 aspect-square flex flex-col animate-fade-in-up delay-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">Top 10 Machine (Downtime)</h2>
                    <a href="{{ $dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View →</a>
                </div>
                @if($topMachines->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="machineDowntimeChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Top 5 MTTR -->
            <div class="chart-card rounded-xl shadow-lg p-6 aspect-square flex flex-col animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Top 5 MTTR (Highest)</h2>
                    <a href="{{ $dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View →</a>
                </div>
                @if($topMTTR->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="mttrChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Top 5 Plant Downtime -->
            <div class="chart-card rounded-xl shadow-lg p-6 aspect-square flex flex-col animate-fade-in-up delay-400">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-green-600 to-teal-600 bg-clip-text text-transparent">Top 5 Plant (Downtime)</h2>
                    <a href="{{ $dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View →</a>
                </div>
                @if($topPlants->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="plantDowntimeChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- Chart Grid - Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Downtime Trend -->
            <div class="chart-card rounded-xl shadow-lg p-6 lg:col-span-2 animate-fade-in-up delay-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Downtime Trend ({{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }})</h2>
                    <a href="{{ $dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View →</a>
                </div>
                @if($downtimeTrend->count() > 0)
                <div class="h-64">
                    <canvas id="downtimeTrendChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Top 5 Problems -->
            <div class="chart-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">Top 5 Problems</h2>
                    <a href="{{ $dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View →</a>
                </div>
                @if($topProblems->count() > 0)
                <div class="h-64">
                    <canvas id="problemsChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- Additional Information Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Most Active Mekanik -->
            <div class="info-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-300">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Most Active Mekanik
                </h3>
                <div class="space-y-3">
                    @forelse($topMekanik->take(3) as $index => $mekanik)
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-sm mr-3">{{ $index + 1 }}</span>
                            <span class="font-semibold text-gray-800">{{ $mekanik->nameMekanik ?? 'N/A' }}</span>
                        </div>
                        <span class="text-sm font-bold text-purple-600">{{ $mekanik->downtime_count }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No data</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Lines -->
            <div class="info-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Top Lines
                </h3>
                <div class="space-y-3">
                    @forelse($topLines->take(3) as $index => $line)
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm mr-3">{{ $index + 1 }}</span>
                            <span class="font-semibold text-gray-800">{{ $line->line ?? 'N/A' }}</span>
                        </div>
                        <span class="text-sm font-bold text-blue-600">{{ number_format((float)($line->total_duration ?? 0), 0) }}m</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No data</p>
                    @endforelse
                </div>
            </div>

            <!-- Longest Downtime -->
            <div class="info-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-500">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Longest Downtime
                </h3>
                @if($longestDowntime)
                <div class="space-y-3">
                    <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 mb-1">Machine</p>
                        <p class="text-lg font-bold text-gray-900">{{ $longestDowntime->idMachine ?? 'N/A' }}</p>
                    </div>
                    <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 mb-1">Duration</p>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-red-600">{{ number_format((float)($longestDowntime->duration ?? 0), 1) }} <span class="text-xs sm:text-sm">min</span></p>
                    </div>
                    <div class="p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 mb-1">Date</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $longestDowntime->date ? \Carbon\Carbon::parse($longestDowntime->date)->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="info-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-600">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Quick Stats
                </h3>
                <div class="space-y-3">
                    <div class="p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Days in Month</p>
                        <p class="text-lg sm:text-xl font-bold text-green-600">{{ $daysInMonth }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Avg per Day</p>
                        <p class="text-lg sm:text-xl font-bold text-green-600">{{ number_format($avgDowntimePerDay, 1) }}m</p>
                    </div>
                    @if($monthDowntimeCount > 0)
                    <div class="p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Breakdowns per Day</p>
                        <p class="text-lg sm:text-xl font-bold text-green-600">{{ number_format($monthDowntimeCount / $daysInMonth, 1) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Downtime Events -->
        <div class="info-card rounded-xl shadow-lg p-6 animate-fade-in-up delay-600">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg sm:text-xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recent Downtime Events ({{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }})
                </h2>
                <a href="{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}" class="text-sm text-blue-600 hover:text-blue-800 font-semibold transition-all hover:translate-x-1">View all →</a>
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                @forelse($recentDowntimeErps as $index => $downtimeItem)
                <div class="downtime-item border-l-4 border-red-500 pl-4 py-4 rounded-lg animate-slide-in-left" style="animation-delay: {{ $index * 0.05 }}s; opacity: 0;">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">{{ $index + 1 }}</span>
                                @if($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp')
                                    <p class="text-lg font-bold text-gray-900">{{ $downtimeItem->idMachine ?? 'N/A' }}</p>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">{{ $downtimeItem->typeMachine ?? 'N/A' }}</span>
                                @else
                                    <p class="text-lg font-bold text-gray-900">{{ $downtimeItem->machine->idMachine ?? 'N/A' }}</p>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">{{ $downtimeItem->machine->machineType->name ?? 'N/A' }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 mt-2 flex-wrap">
                                @if($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        🏭 {{ $downtimeItem->plant ?? 'N/A' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        ⚠️ {{ strlen($downtimeItem->problemDowntime ?? 'N/A') > 30 ? substr($downtimeItem->problemDowntime, 0, 30) . '...' : ($downtimeItem->problemDowntime ?? 'N/A') }}
                                </span>
                                    @if($downtimeItem->nameMekanik)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        👤 {{ $downtimeItem->nameMekanik }}
                                </span>
                                @endif
                                    @if($downtimeItem->line)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        📍 {{ $downtimeItem->line }}
                                </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        🏭 {{ $downtimeItem->machine->plant->name ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        ⚠️ {{ strlen($downtimeItem->problem->name ?? 'N/A') > 30 ? substr($downtimeItem->problem->name, 0, 30) . '...' : ($downtimeItem->problem->name ?? 'N/A') }}
                                    </span>
                                    @if($downtimeItem->mekanik)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        👤 {{ $downtimeItem->mekanik->name }}
                                    </span>
                                    @endif
                                    @if($downtimeItem->machine->line)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        📍 {{ $downtimeItem->machine->line->name }}
                                    </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-xs text-gray-500 mb-1">{{ $downtimeItem->date ? \Carbon\Carbon::parse($downtimeItem->date)->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">{{ number_format((float)($downtimeItem->duration ?? 0), 1) }}</p>
                            <p class="text-xs text-gray-600">minutes</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No recent downtime events</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.register(ChartDataLabels);

    // ========== TOP 10 MACHINE DOWNTIME (PIE CHART) ==========
    const machineCtx = document.getElementById('machineDowntimeChart');
    if (machineCtx) {
        const machineData = @json($topMachines);
        if (machineData.length > 0) {
            const labels = machineData.map(m => m.idMachine);
            const durations = machineData.map(m => parseFloat(m.total_duration) || 0);
            const typeNames = machineData.map(m => m.typeMachine || 'N/A');
            const colors = [
                '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16',
                '#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#3B82F6'
            ];

            new Chart(machineCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Downtime (minutes)',
                        data: durations,
                        backgroundColor: colors.slice(0, machineData.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    layout: {
                        padding: { top: 10, bottom: 10, left: 10, right: 10 }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return typeNames[index] || 'N/A';
                                },
                                label: function(context) {
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${Math.round(value)} min (${percentage}%)`;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            color: '#ffffff',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value, context) {
                                return context.chart.data.labels[context.dataIndex];
                            }
                        }
                    }
                }
            });
        }
    }

    // ========== TOP 5 MTTR (HORIZONTAL BAR CHART) ==========
    const mttrCtx = document.getElementById('mttrChart');
    if (mttrCtx) {
        const mttrData = @json($topMTTR);
        if (mttrData.length > 0) {
            const mttrLabels = mttrData.map(m => m.idMachine);
            const mttrValues = mttrData.map(m => parseFloat(m.mttr) || 0);
            const mttrTypes = mttrData.map(m => m.typeMachine || 'N/A');
            const mttrCounts = mttrData.map(m => m.downtime_count || 0);
            const mttrColors = ['#EF4444', '#F97316', '#FB923C', '#FBBF24', '#84CC16'];

            const mttrChart = new Chart(mttrCtx, {
                type: 'bar',
                data: {
                    labels: mttrLabels,
                    datasets: [{
                        label: 'MTTR (minutes)',
                        data: mttrValues,
                        backgroundColor: mttrColors,
                        borderColor: mttrColors,
                        borderWidth: 1,
                        barThickness: 'flex',
                        maxBarThickness: 50
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart',
                        onComplete: function() {
                            drawTextInBars(mttrChart, mttrLabels, mttrTypes, mttrValues, mttrCounts);
                        }
                    },
                    layout: {
                        padding: { left: 10, right: 10, top: 15, bottom: 35 }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MTTR (minutes)',
                                font: { size: 11, weight: 'bold' },
                                padding: { top: 3 }
                            },
                            ticks: {
                                callback: function(value) { return Math.round(value); },
                                font: { size: 10 },
                                stepSize: 5
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        },
                        y: {
                            title: { display: false },
                            ticks: { display: false },
                            grid: { display: false },
                            categoryPercentage: 0.8,
                            barPercentage: 0.6
                        }
                    },
                }
            });
            
            function drawTextInBars(chart, labels, types, values, counts) {
                const ctx = chart.canvas.getContext('2d');
                const meta = chart.getDatasetMeta(0);
                const chartArea = chart.chartArea;
                
                ctx.save();
                ctx.font = 'bold 11px Arial';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                
                meta.data.forEach((bar, index) => {
                    if (!bar) return;
                    const idMachine = labels[index];
                    const typeMachine = types[index] || 'N/A';
                    const mttr = Math.round(values[index]);
                    const downtimeCount = counts[index];
                    
                    const barY = bar.y;
                    const textX = chartArea.left + 15;
                    const textY1 = barY;
                    const textY2 = barY + 18;
                    
                    ctx.strokeStyle = '#000000'; 
                    ctx.lineWidth = 2;
                    ctx.lineJoin = 'round';
                    ctx.miterLimit = 2;
                    
                    const line1 = idMachine + ' / ' + typeMachine;
                    ctx.strokeText(line1, textX, textY1);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(line1, textX, textY1);
                    
                    const line2 = 'MTTR : ' + mttr + ' min / ' + downtimeCount + 'x downtime';
                    ctx.strokeText(line2, textX, textY2);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(line2, textX, textY2);
                });
                
                ctx.restore();
            }
            
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (mttrChart) mttrChart.update('none');
                }, 250);
            });
        }
    }

    // ========== TOP 5 PLANT DOWNTIME (HORIZONTAL BAR CHART) ==========
    const plantCtx = document.getElementById('plantDowntimeChart');
    if (plantCtx) {
        const plantData = @json($topPlants);
        if (plantData.length > 0) {
            const plantLabels = plantData.map(p => p.plant || 'N/A');
            const plantDurations = plantData.map(p => parseFloat(p.total_duration) || 0);
            const plantCounts = plantData.map(p => p.downtime_count || 0);
            const plantColors = ['#EF4444', '#F97316', '#FB923C', '#FBBF24', '#84CC16'];

            const plantChart = new Chart(plantCtx, {
                type: 'bar',
                data: {
                    labels: plantLabels,
                    datasets: [{
                        label: 'Downtime (minutes)',
                        data: plantDurations,
                        backgroundColor: plantColors,
                        borderColor: plantColors,
                        borderWidth: 1,
                        barThickness: 'flex',
                        maxBarThickness: 50
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart',
                        onComplete: function() {
                            drawTextInPlantBars(plantChart, plantLabels, plantDurations, plantCounts);
                        }
                    },
                    layout: {
                        padding: { left: 10, right: 10, top: 15, bottom: 35 }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label || 'N/A';
                                },
                                label: function(context) {
                                    const value = context.parsed.x || 0;
                                    const index = context.dataIndex;
                                    const count = plantCounts[index] || 0;
                                    return [
                                        `Duration: ${Math.round(value)} min`,
                                        `Downtime Count: ${count}`
                                    ];
                                }
                            }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Downtime (minutes)',
                                font: { size: 11, weight: 'bold' },
                                padding: { top: 3 }
                            },
                            ticks: {
                                callback: function(value) { return Math.round(value); },
                                font: { size: 10 },
                                stepSize: 50
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        },
                        y: {
                            title: { display: false },
                            ticks: { display: false },
                            grid: { display: false },
                            categoryPercentage: 0.8,
                            barPercentage: 0.6
                        }
                    },
                }
            });
            
            function drawTextInPlantBars(chart, labels, durations, counts) {
                const ctx = chart.canvas.getContext('2d');
                const meta = chart.getDatasetMeta(0);
                const chartArea = chart.chartArea;
                
                ctx.save();
                ctx.font = 'bold 11px Arial';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                
                meta.data.forEach((bar, index) => {
                    if (!bar) return;
                    const plantName = labels[index] || 'N/A';
                    const duration = Math.round(durations[index]);
                    const downtimeCount = counts[index];
                    
                    const barY = bar.y;
                    const textX = chartArea.left + 15;
                    const textY1 = barY;
                    const textY2 = barY + 18;
                    
                    ctx.strokeStyle = '#000000'; 
                    ctx.lineWidth = 2;
                    ctx.lineJoin = 'round';
                    ctx.miterLimit = 2;
                    
                    const line1 = plantName + ' / ' + duration + ' min';
                    ctx.strokeText(line1, textX, textY1);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(line1, textX, textY1);
                    
                    const line2 = 'Downtime Count: ' + downtimeCount;
                    ctx.strokeText(line2, textX, textY2);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(line2, textX, textY2);
                });
                
                ctx.restore();
            }
            
            let resizeTimerPlant;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimerPlant);
                resizeTimerPlant = setTimeout(function() {
                    if (plantChart) plantChart.update('none');
                }, 250);
            });
        }
    }

    // ========== DOWNTIME TREND (LINE CHART) ==========
    const trendCtx = document.getElementById('downtimeTrendChart');
    if (trendCtx) {
        const trendData = @json($downtimeTrend);
        if (trendData.length > 0) {
            const trendLabels = trendData.map(t => {
                const date = new Date(t.date);
                return date.getDate().toString().padStart(2, '0') + '/' + (date.getMonth() + 1).toString().padStart(2, '0');
            });
            const trendCounts = trendData.map(t => t.count || 0);
            const trendDurations = trendData.map(t => parseFloat(t.total_duration) || 0);

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Downtime Count',
                            data: trendCounts,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Duration (minutes)',
                            data: trendDurations,
                            borderColor: '#f5576c',
                            backgroundColor: 'rgba(245, 87, 108, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                font: { size: 12, weight: 'bold' }
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Count',
                                font: { size: 12, weight: 'bold' }
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Duration (minutes)',
                                font: { size: 12, weight: 'bold' }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    }

    // ========== TOP 5 PROBLEMS (VERTICAL BAR CHART) ==========
    const problemsCtx = document.getElementById('problemsChart');
    if (problemsCtx) {
        const problemsData = @json($topProblems);
        if (problemsData.length > 0) {
            const problemsLabels = problemsData.map(p => {
                const problem = p.problemDowntime || 'N/A';
                return problem.length > 20 ? problem.substring(0, 20) + '...' : problem;
            });
            const problemsCounts = problemsData.map(p => p.problem_count || 0);
            const problemsColors = ['#EF4444', '#F97316', '#FB923C', '#FBBF24', '#84CC16'];

            new Chart(problemsCtx, {
                type: 'bar',
                data: {
                    labels: problemsLabels,
                    datasets: [{
                        label: 'Count',
                        data: problemsCounts,
                        backgroundColor: problemsColors,
                        borderColor: problemsColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return problemsData[index].problemDowntime || 'N/A';
                                },
                                label: function(context) {
                                    const value = context.parsed.x || 0;
                                    return `Count: ${value}`;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            color: '#ffffff',
                            font: { weight: 'bold', size: 10 },
                            anchor: 'end',
                            align: 'right'
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Count',
                                font: { size: 11, weight: 'bold' }
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y: {
                            title: { display: false },
                            grid: { display: false }
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
