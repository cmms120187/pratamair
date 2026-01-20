@extends('layouts.app')
@section('content')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
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
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .chart-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        border-color: #667eea;
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.15);
        transform: translateY(-3px);
    }
    .info-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .delay-100 { animation-delay: 0.1s; opacity: 0; }
    .delay-200 { animation-delay: 0.2s; opacity: 0; }
    .delay-300 { animation-delay: 0.3s; opacity: 0; }
    .delay-400 { animation-delay: 0.4s; opacity: 0; }
    .delay-500 { animation-delay: 0.5s; opacity: 0; }
    .delay-600 { animation-delay: 0.6s; opacity: 0; }
    
    /* Optimized for Large Monitors (50 inch+) */
    @media (min-width: 1920px) {
        .large-screen-grid { grid-template-columns: repeat(8, minmax(0, 1fr)); }
        .large-screen-text-xl { font-size: 1.5rem; line-height: 2rem; }
        .large-screen-text-2xl { font-size: 2rem; line-height: 2.5rem; }
        .large-screen-text-3xl { font-size: 3rem; line-height: 3.5rem; }
        .large-screen-text-4xl { font-size: 4rem; line-height: 4.5rem; }
        .large-screen-text-5xl { font-size: 5rem; line-height: 5.5rem; }
        .large-screen-padding { padding: 1.5rem; }
    }
</style>

<div class="w-full p-3 xl:p-4 2xl:p-6 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="w-full mx-auto max-w-[100%]">
        <!-- Header - Optimized for Large Screen -->
        <div class="mb-4 xl:mb-6 2xl:mb-8 animate-fade-in">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 xl:gap-4">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h1 class="text-2xl xl:text-3xl 2xl:text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                                Dashboard Large View
                            </h1>
                            <p class="text-sm xl:text-base 2xl:text-lg text-gray-600 font-medium">
                                {{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}
                            </p>
                        </div>
                        <a href="{{ route('dashboard', ['month' => $filterMonth, 'year' => $filterYear, 'data_source' => $dataSource]) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition text-sm xl:text-base whitespace-nowrap">
                            Dashboard Normal
                        </a>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 xl:gap-3">
                    <!-- Filter Bulan dan Tahun -->
                    <form method="GET" action="{{ route('dashboard.large') }}" class="flex items-center gap-2" id="filterForm">
                        <input type="hidden" name="data_source" value="{{ $dataSource }}">
                        <label for="month" class="text-xs xl:text-sm font-semibold text-gray-700 whitespace-nowrap">Bulan:</label>
                        <select name="month" id="month" 
                                onchange="document.getElementById('filterForm').submit();"
                                class="px-2 xl:px-3 py-1.5 xl:py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium text-xs xl:text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $filterMonth == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        <label for="year" class="text-xs xl:text-sm font-semibold text-gray-700 whitespace-nowrap">Tahun:</label>
                        <select name="year" id="year" 
                                onchange="document.getElementById('filterForm').submit();"
                                class="px-2 xl:px-3 py-1.5 xl:py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium text-xs xl:text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                    <!-- Data Source -->
                    <form method="GET" action="{{ route('dashboard.large') }}" class="inline-block" id="dataSourceForm">
                        <input type="hidden" name="month" value="{{ $filterMonth }}">
                        <input type="hidden" name="year" value="{{ $filterYear }}">
                        <label for="data_source" class="text-xs xl:text-sm font-semibold text-gray-700 whitespace-nowrap">Data Source:</label>
                        <select name="data_source" id="data_source" 
                                onchange="document.getElementById('dataSourceForm').submit();"
                                class="px-3 xl:px-4 py-1.5 xl:py-2 border border-gray-300 rounded-lg shadow-sm bg-white text-gray-700 font-medium text-xs xl:text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="downtime_erp2" {{ $dataSource === 'downtime_erp2' ? 'selected' : '' }}>Downtime ERP2</option>
                            <option value="downtime_erp" {{ $dataSource === 'downtime_erp' ? 'selected' : '' }}>Downtime ERP</option>
                            <option value="downtime" {{ $dataSource === 'downtime' ? 'selected' : '' }}>Downtime</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Top Statistics Cards - Optimized for Large Screen (6-8 columns) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8 gap-3 xl:gap-4 2xl:gap-5 mb-5 xl:mb-6 2xl:mb-8">
            <!-- Total Downtime Count -->
            <div class="stat-card stat-card-1 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-100 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Total Breakdowns</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ number_format($monthDowntimeCount) }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('M Y') }}</p>
                </div>
            </div>
            
            <!-- Total Duration -->
            <div class="stat-card stat-card-2 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-200 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Total Duration</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ number_format($monthDowntime, 0) }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">minutes</p>
                </div>
            </div>

            <!-- Average Duration -->
            <div class="stat-card stat-card-3 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-300 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Avg/Breakdown</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ number_format($avgDowntimeDuration, 1) }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">minutes</p>
                </div>
            </div>

            <!-- Average per Day -->
            <div class="stat-card stat-card-4 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-400 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Avg per Day</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ number_format($avgDowntimePerDay, 1) }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">min/day</p>
                </div>
            </div>

            <!-- Most Problematic Machine -->
            <div class="stat-card stat-card-5 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-500 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Top Machine</p>
                    @if($mostProblematicMachine)
                        <p class="text-sm xl:text-base 2xl:text-lg font-bold mt-1 xl:mt-2 truncate" title="{{ $mostProblematicMachine->idMachine }}">{{ $mostProblematicMachine->idMachine }}</p>
                        <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">{{ number_format((float)($mostProblematicMachine->total_duration ?? 0), 0) }} min</p>
                    @else
                        <p class="text-sm xl:text-base 2xl:text-lg font-bold mt-1 xl:mt-2">-</p>
                        <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">No data</p>
                    @endif
                </div>
            </div>
            
            <!-- Additional Quick Stats - New Cards for Large Screen -->
            <!-- Days in Month -->
            <div class="stat-card stat-card-1 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-500 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Days in Month</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ $daysInMonth }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">days</p>
                </div>
            </div>
            
            <!-- Breakdowns per Day -->
            <div class="stat-card stat-card-2 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-500 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Breakdowns/Day</p>
                    <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ $monthDowntimeCount > 0 ? number_format($monthDowntimeCount / $daysInMonth, 1) : '0' }}</p>
                    <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">per day</p>
                </div>
            </div>
            
            <!-- Longest Downtime Duration -->
            <div class="stat-card stat-card-3 rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 text-white animate-fade-in-up delay-500 hover:shadow-xl">
                <div class="flex items-center justify-between mb-2 xl:mb-3">
                    <div class="bg-white/20 backdrop-blur-sm p-2 xl:p-3 rounded-full">
                        <svg class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
<div>
                    <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-white/80 mb-1">Longest DT</p>
                    @if($longestDowntime)
                        <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">{{ number_format((float)($longestDowntime->duration ?? 0), 0) }}</p>
                        <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1 truncate" title="{{ $longestDowntime->idMachine ?? 'N/A' }}">{{ $longestDowntime->idMachine ?? 'N/A' }}</p>
                    @else
                        <p class="text-xl xl:text-2xl 2xl:text-3xl font-bold mt-1 xl:mt-2">-</p>
                        <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-white/70 mt-1">No data</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Chart Grid - Optimized for Large Screen (4-6 columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 xl:gap-4 2xl:gap-5 mb-5 xl:mb-6 2xl:mb-8">
            <!-- Top 10 Machine Downtime -->
            <div class="chart-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 xl:col-span-2 2xl:col-span-3 flex flex-col animate-fade-in-up delay-200 cursor-pointer" style="min-height: 320px;" title="Klik untuk melihat detail downtime mesin" onclick="window.location.href='{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}'">
                <div class="flex items-center justify-between mb-3 xl:mb-4">
                    <h2 class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">Top 10 Machine (Downtime)</h2>
                </div>
                @if($topMachines->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="machineDowntimeChart"></canvas>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No data available</p>
                @endif
            </div>

            <!-- Top 5 MTTR -->
            <div class="chart-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 xl:col-span-2 2xl:col-span-3 flex flex-col animate-fade-in-up delay-300 cursor-pointer" style="min-height: 320px;" title="Klik untuk melihat detail MTTR mesin" onclick="window.location.href='{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}'">
                <div class="flex items-center justify-between mb-3 xl:mb-4">
                    <h2 class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Top 5 MTTR (Highest)</h2>
                </div>
                @if($topMTTR->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="mttrChart"></canvas>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No data available</p>
                @endif
            </div>
        </div>
        
        <!-- Chart Grid Row 2 - Optimized for Large Screen -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 xl:gap-4 2xl:gap-5 mb-5 xl:mb-6 2xl:mb-8">
            <!-- Top 5 Plant Downtime -->
            <div class="chart-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 xl:col-span-2 2xl:col-span-2 flex flex-col animate-fade-in-up delay-400 cursor-pointer" style="min-height: 320px;" title="Klik untuk melihat detail downtime plant" onclick="window.location.href='{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}'">
                <div class="flex items-center justify-between mb-3 xl:mb-4">
                    <h2 class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-green-600 to-teal-600 bg-clip-text text-transparent">Top 5 Plant (Downtime)</h2>
                </div>
                @if($topPlants->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="plantDowntimeChart"></canvas>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No data available</p>
                @endif
            </div>

            <!-- Downtime Trend -->
            <div class="chart-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 xl:col-span-2 2xl:col-span-4 animate-fade-in-up delay-500 cursor-pointer" title="Klik untuk melihat detail trend downtime" onclick="window.location.href='{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}'">
                <div class="flex items-center justify-between mb-3 xl:mb-4">
                    <h2 class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Downtime Trend ({{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }})</h2>
                </div>
                @if($downtimeTrend->count() > 0)
                <div class="h-48 xl:h-56 2xl:h-64">
                    <canvas id="downtimeTrendChart"></canvas>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No data available</p>
                @endif
            </div>
        </div>
        
        <!-- Chart Grid Row 3 - Optimized for Large Screen -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 xl:gap-4 2xl:gap-5 mb-5 xl:mb-6 2xl:mb-8">
            <!-- Top 5 Problems -->
            <div class="chart-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 xl:col-span-2 2xl:col-span-2 animate-fade-in-up delay-500 cursor-pointer" title="Klik untuk melihat detail masalah downtime" onclick="window.location.href='{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}'">
                <div class="flex items-center justify-between mb-3 xl:mb-4">
                    <h2 class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">Top 5 Problems</h2>
                </div>
                @if($topProblems->count() > 0)
                <div class="h-48 xl:h-56 2xl:h-64">
                    <canvas id="problemsChart"></canvas>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No data available</p>
                @endif
            </div>
            
            <!-- Additional Information Cards - Optimized for Large Screen (4 columns) -->
            <!-- Most Active Mekanik -->
            <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-300">
                <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                    <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="hidden xl:inline">Most Active Mekanik</span>
                    <span class="xl:hidden">Top Mekanik</span>
                </h3>
                <div class="space-y-2 xl:space-y-3">
                    @forelse($topMekanik->take(5) as $index => $mekanik)
                    <div class="flex items-center justify-between p-2 xl:p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                        <div class="flex items-center flex-1 min-w-0">
                            <span class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold text-[10px] xl:text-xs 2xl:text-sm mr-2 xl:mr-3 flex-shrink-0">{{ $index + 1 }}</span>
                            <span class="font-semibold text-gray-800 text-xs xl:text-sm 2xl:text-base truncate">{{ $mekanik->nameMekanik ?? 'N/A' }}</span>
                        </div>
                        <span class="text-xs xl:text-sm 2xl:text-base font-bold text-purple-600 ml-2 flex-shrink-0">{{ $mekanik->downtime_count }}</span>
                    </div>
                    @empty
                    <p class="text-xs xl:text-sm text-gray-500 text-center py-2">No data</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Lines -->
            <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-400">
                <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                    <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Top Lines
                </h3>
                <div class="space-y-2 xl:space-y-3">
                    @forelse($topLines->take(5) as $index => $line)
                    <div class="flex items-center justify-between p-2 xl:p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                        <div class="flex items-center flex-1 min-w-0">
                            <span class="w-6 xl:w-7 2xl:w-8 h-6 xl:h-7 2xl:h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px] xl:text-xs 2xl:text-sm mr-2 xl:mr-3 flex-shrink-0">{{ $index + 1 }}</span>
                            <span class="font-semibold text-gray-800 text-xs xl:text-sm 2xl:text-base truncate">{{ $line->line ?? 'N/A' }}</span>
                        </div>
                        <span class="text-xs xl:text-sm 2xl:text-base font-bold text-blue-600 ml-2 flex-shrink-0">{{ number_format((float)($line->total_duration ?? 0), 0) }}m</span>
                    </div>
                    @empty
                    <p class="text-xs xl:text-sm text-gray-500 text-center py-2">No data</p>
                    @endforelse
                </div>
            </div>

            <!-- Longest Downtime -->
            <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-500">
                <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                    <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="hidden xl:inline">Longest Downtime</span>
                    <span class="xl:hidden">Longest DT</span>
                </h3>
                @if($longestDowntime)
                <div class="space-y-2 xl:space-y-3">
                    <div class="p-3 xl:p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-gray-600 mb-1">Machine</p>
                        <p class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-900 truncate" title="{{ $longestDowntime->idMachine ?? 'N/A' }}">{{ $longestDowntime->idMachine ?? 'N/A' }}</p>
                    </div>
                    <div class="p-3 xl:p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-gray-600 mb-1">Duration</p>
                        <p class="text-lg xl:text-xl 2xl:text-2xl font-bold text-red-600">{{ number_format((float)($longestDowntime->duration ?? 0), 1) }} <span class="text-[9px] xl:text-xs 2xl:text-sm">min</span></p>
                    </div>
                    <div class="p-3 xl:p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs 2xl:text-sm font-medium text-gray-600 mb-1">Date</p>
                        <p class="text-xs xl:text-sm 2xl:text-base font-semibold text-gray-900">{{ $longestDowntime->date ? \Carbon\Carbon::parse($longestDowntime->date)->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs xl:text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-600">
                <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                    <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Quick Stats
                </h3>
                <div class="space-y-2 xl:space-y-3">
                    <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Days in Month</p>
                        <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ $daysInMonth }}</p>
                    </div>
                    <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Avg per Day</p>
                        <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($avgDowntimePerDay, 1) }}m</p>
                    </div>
                    @if($monthDowntimeCount > 0)
                    <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Breakdowns/Day</p>
                        <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($monthDowntimeCount / $daysInMonth, 1) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Comprehensive Statistics Section - Optimized for Large Screen (6-8 columns) -->
        <div class="mb-5 xl:mb-6 2xl:mb-8">
            <h2 class="text-xl xl:text-2xl 2xl:text-3xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-4 xl:mb-6 animate-fade-in-up">
                <i class="fas fa-chart-pie mr-2"></i>Comprehensive System Statistics
            </h2>
            
            <!-- Row 1: Sparepart, Location, Problem/Reason/Action -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 xl:gap-4 2xl:gap-5 mb-4 xl:mb-5 2xl:mb-6">
                <!-- Sparepart Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-100">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Sparepart
                    </h3>
                    <div class="space-y-2 xl:space-y-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Total</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-orange-600">{{ number_format($totalSpareparts ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Low Stock</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-red-600">{{ number_format($lowStockSpareparts ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Stock Value</p>
                            <p class="text-sm xl:text-base 2xl:text-lg font-bold text-green-600">Rp {{ number_format($totalStockValue ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Location Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-200">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Location
                    </h3>
                    <div class="grid grid-cols-2 gap-2 xl:gap-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Plants</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($totalPlants ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Processes</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-blue-600">{{ number_format($totalProcesses ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Lines</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-purple-600">{{ number_format($totalLines ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Rooms</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-yellow-600">{{ number_format($totalRooms ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Problem/Reason/Action Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-300">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Problem/Reason/Action
                    </h3>
                    <div class="space-y-2 xl:space-y-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Problems</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-red-600">{{ number_format($uniqueProblems ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Reasons</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-blue-600">{{ number_format($uniqueReasons ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-teal-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Actions</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($uniqueActions ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Problem MMs</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-purple-600">{{ number_format($uniqueProblemMms ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Machines Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-400">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        Machines
                    </h3>
                    <div class="space-y-2 xl:space-y-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Total</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-blue-600">{{ number_format($totalMachines ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-orange-50 to-red-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">With Downtime</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-orange-600">{{ number_format($machinesWithDowntime ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">With PM</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($machinesWithPM ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- SDM Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-500">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        SDM (HR)
                    </h3>
                    <div class="space-y-2 xl:space-y-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Total Users</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-indigo-600">{{ number_format($totalUsers ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Mechanics</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-blue-600">{{ number_format($totalMechanics ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-teal-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Active</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($activeMechanics ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Standards Statistics -->
                <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-600">
                    <h3 class="text-sm xl:text-base 2xl:text-lg font-bold text-gray-800 mb-3 xl:mb-4 flex items-center">
                        <svg class="w-4 xl:w-5 2xl:w-6 h-4 xl:h-5 2xl:h-6 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Standards (PdM)
                    </h3>
                    <div class="space-y-2 xl:space-y-3">
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-teal-50 to-cyan-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Total</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-teal-600">{{ number_format($totalStandards ?? 0) }}</p>
                        </div>
                        <div class="p-2 xl:p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-[10px] xl:text-xs text-gray-600 mb-1">Active</p>
                            <p class="text-base xl:text-lg 2xl:text-xl font-bold text-green-600">{{ number_format($activeStandards ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Downtime Events - Optimized for Large Screen -->
        <div class="info-card rounded-xl shadow-lg p-4 xl:p-5 2xl:p-6 animate-fade-in-up delay-600 mb-5 xl:mb-6 2xl:mb-8">
            <div class="flex items-center justify-between mb-4 xl:mb-6">
                <h2 class="text-base xl:text-lg 2xl:text-xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent flex items-center">
                    <svg class="w-5 xl:w-6 2xl:w-7 h-5 xl:h-6 2xl:h-7 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recent Downtime Events ({{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }})
                </h2>
                <a href="{{ $dataSource === 'downtime_erp2' ? route('downtime-erp2.index') : ($dataSource === 'downtime_erp' ? route('downtime_erp.index') : route('downtimes.index')) }}" class="text-xs xl:text-sm 2xl:text-base text-blue-600 hover:text-blue-800 font-semibold transition-all hover:translate-x-1">View all →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 xl:gap-4 max-h-[600px] overflow-y-auto pr-2">
                @forelse($recentDowntimeErps as $index => $downtimeItem)
                <div class="border-l-4 border-red-500 pl-3 xl:pl-4 py-3 xl:py-4 rounded-lg bg-gradient-to-r from-white to-gray-50 hover:shadow-md transition-all animate-fade-in-up" style="animation-delay: {{ $index * 0.05 }}s; opacity: 0;">
                    <div class="flex items-start justify-between mb-2">
                        <span class="px-2 xl:px-3 py-1 bg-red-100 text-red-800 rounded-full text-[10px] xl:text-xs 2xl:text-sm font-bold">{{ $index + 1 }}</span>
                        @if($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp')
                            <p class="text-xs xl:text-sm 2xl:text-base font-bold text-gray-900 truncate flex-1 ml-2" title="{{ $downtimeItem->idMachine ?? 'N/A' }}">{{ $downtimeItem->idMachine ?? 'N/A' }}</p>
                        @else
                            <p class="text-xs xl:text-sm 2xl:text-base font-bold text-gray-900 truncate flex-1 ml-2" title="{{ $downtimeItem->machine->idMachine ?? 'N/A' }}">{{ $downtimeItem->machine->idMachine ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div class="space-y-1.5 xl:space-y-2">
                        @if($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp')
                        <div class="flex flex-wrap gap-1 xl:gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-blue-100 text-blue-800 truncate max-w-full" title="{{ $downtimeItem->plant ?? 'N/A' }}">
                                🏭 {{ $downtimeItem->plant ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-purple-100 text-purple-800 truncate max-w-full" title="{{ $downtimeItem->problemDowntime ?? 'N/A' }}">
                                ⚠️ {{ strlen($downtimeItem->problemDowntime ?? 'N/A') > 25 ? substr($downtimeItem->problemDowntime, 0, 25) . '...' : ($downtimeItem->problemDowntime ?? 'N/A') }}
                            </span>
                            @if($downtimeItem->nameMekanik)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-green-100 text-green-800 truncate max-w-full">
                                👤 {{ $downtimeItem->nameMekanik }}
                            </span>
                            @endif
                        </div>
                        @else
                        <div class="flex flex-wrap gap-1 xl:gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-blue-100 text-blue-800 truncate max-w-full" title="{{ $downtimeItem->machine->plant->name ?? 'N/A' }}">
                                🏭 {{ $downtimeItem->machine->plant->name ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-purple-100 text-purple-800 truncate max-w-full" title="{{ $downtimeItem->problem->name ?? 'N/A' }}">
                                ⚠️ {{ strlen($downtimeItem->problem->name ?? 'N/A') > 25 ? substr($downtimeItem->problem->name, 0, 25) . '...' : ($downtimeItem->problem->name ?? 'N/A') }}
                            </span>
                            @if($downtimeItem->mekanik)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] xl:text-[10px] 2xl:text-xs font-medium bg-green-100 text-green-800 truncate max-w-full">
                                👤 {{ $downtimeItem->mekanik->name }}
                            </span>
                            @endif
                        </div>
                        @endif
                        <div class="flex items-center justify-between pt-1 xl:pt-2 border-t border-gray-200">
                            <p class="text-[9px] xl:text-[10px] 2xl:text-xs text-gray-500">{{ $downtimeItem->date ? \Carbon\Carbon::parse($downtimeItem->date)->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-sm xl:text-base 2xl:text-lg font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">{{ number_format((float)($downtimeItem->duration ?? 0), 1) }} <span class="text-[9px] xl:text-[10px] 2xl:text-xs">min</span></p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full">
                    <p class="text-xs xl:text-sm text-gray-500 text-center py-8">No recent downtime events</p>
                </div>
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
    
    // Same chart logic as dashboard.blade.php but optimized for larger displays
    // Chart implementations here - reuse from dashboard.blade.php
    const machineCtx = document.getElementById('machineDowntimeChart');
    if (machineCtx) {
        const machineData = @json($topMachines);
        if (machineData.length > 0) {
            const labels = machineData.map(m => m.idMachine);
            const durations = machineData.map(m => parseFloat(m.total_duration) || 0);
            const typeNames = machineData.map(m => m.typeMachine || 'N/A');
            const colors = ['#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#3B82F6'];
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
                    animation: { duration: 2000, easing: 'easeOutQuart' },
                    layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } },
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
                            font: { weight: 'bold', size: 10 },
                            formatter: function(value, context) {
                                return context.chart.data.labels[context.dataIndex];
                            }
                        }
                    }
                }
            });
        }
    }
    
    // MTTR Chart
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
                    layout: { padding: { left: 10, right: 10, top: 15, bottom: 35 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'MTTR (minutes)', font: { size: 11, weight: 'bold' }, padding: { top: 3 } },
                            ticks: { callback: function(value) { return Math.round(value); }, font: { size: 10 }, stepSize: 5 },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        },
                        y: {
                            title: { display: false },
                            ticks: { display: false },
                            grid: { display: false },
                            categoryPercentage: 0.8,
                            barPercentage: 0.6
                        }
                    }
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
        }
    }
    
    // Plant Downtime Chart
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
                    layout: { padding: { left: 10, right: 10, top: 15, bottom: 35 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(context) { return context[0].label || 'N/A'; },
                                label: function(context) {
                                    const value = context.parsed.x || 0;
                                    const index = context.dataIndex;
                                    const count = plantCounts[index] || 0;
                                    return [`Duration: ${Math.round(value)} min`, `Downtime Count: ${count}`];
                                }
                            }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Downtime (minutes)', font: { size: 11, weight: 'bold' }, padding: { top: 3 } },
                            ticks: { callback: function(value) { return Math.round(value); }, font: { size: 10 }, stepSize: 50 },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        },
                        y: {
                            title: { display: false },
                            ticks: { display: false },
                            grid: { display: false },
                            categoryPercentage: 0.8,
                            barPercentage: 0.6
                        }
                    }
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
        }
    }
    
    // Downtime Trend Chart
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
                    animation: { duration: 2000, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Date', font: { size: 12, weight: 'bold' } },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Count', font: { size: 12, weight: 'bold' } },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Duration (minutes)', font: { size: 12, weight: 'bold' } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    }
    
    // Problems Chart
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
                    animation: { duration: 1500, easing: 'easeOutQuart' },
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
                            title: { display: true, text: 'Count', font: { size: 11, weight: 'bold' } },
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
