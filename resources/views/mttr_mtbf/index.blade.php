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
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    @keyframes glow-1 {
        0%, 100% {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3), 0 0 40px rgba(118, 75, 162, 0.2);
        }
        50% {
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.6), 0 0 60px rgba(118, 75, 162, 0.4);
        }
    }
    @keyframes glow-2 {
        0%, 100% {
            box-shadow: 0 0 20px rgba(240, 147, 251, 0.3), 0 0 40px rgba(245, 87, 108, 0.2);
        }
        50% {
            box-shadow: 0 0 30px rgba(240, 147, 251, 0.6), 0 0 60px rgba(245, 87, 108, 0.4);
        }
    }
    @keyframes glow-3 {
        0%, 100% {
            box-shadow: 0 0 20px rgba(79, 172, 254, 0.3), 0 0 40px rgba(0, 242, 254, 0.2);
        }
        50% {
            box-shadow: 0 0 30px rgba(79, 172, 254, 0.6), 0 0 60px rgba(0, 242, 254, 0.4);
        }
    }
    @keyframes glow-4 {
        0%, 100% {
            box-shadow: 0 0 20px rgba(67, 233, 123, 0.3), 0 0 40px rgba(56, 249, 215, 0.2);
        }
        50% {
            box-shadow: 0 0 30px rgba(67, 233, 123, 0.6), 0 0 60px rgba(56, 249, 215, 0.4);
        }
    }
    @keyframes glow-5 {
        0%, 100% {
            box-shadow: 0 0 20px rgba(250, 112, 154, 0.3), 0 0 40px rgba(254, 225, 64, 0.2);
        }
        50% {
            box-shadow: 0 0 30px rgba(250, 112, 154, 0.6), 0 0 60px rgba(254, 225, 64, 0.4);
        }
    }
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .delay-100 { animation-delay: 0.1s; opacity: 0; }
    .delay-150 { animation-delay: 0.15s; opacity: 0; }
    .delay-200 { animation-delay: 0.2s; opacity: 0; }
    .delay-250 { animation-delay: 0.25s; opacity: 0; }
    .delay-300 { animation-delay: 0.3s; opacity: 0; }
    .animate-fade-in-up.delay-100,
    .animate-fade-in-up.delay-150,
    .animate-fade-in-up.delay-200,
    .animate-fade-in-up.delay-250,
    .animate-fade-in-up.delay-300 {
        pointer-events: auto !important;
    }
    .stat-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        background-size: 200% 200%;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s;
    }
    .stat-card:hover::before {
        left: 100%;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s;
    }
    .stat-card:hover::after {
        opacity: 1;
    }
    .stat-card-1 { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: glow-1 3s ease-in-out infinite;
    }
    .stat-card-2 { 
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        animation: glow-2 3s ease-in-out infinite 0.5s;
    }
    .stat-card-3 { 
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        animation: glow-3 3s ease-in-out infinite 1s;
    }
    .stat-card-4 { 
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        animation: glow-4 3s ease-in-out infinite 1.5s;
    }
    .stat-card-5 { 
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        animation: glow-5 3s ease-in-out infinite 2s;
    }
    .stat-card:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        animation: pulse 1s ease-in-out infinite;
    }
    .stat-card .bg-white\/20 {
        animation: pulse 2s ease-in-out infinite;
    }
    .stat-card:hover .bg-white\/20 {
        animation: pulse 0.8s ease-in-out infinite;
        transform: rotate(5deg);
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
</style>
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen" x-data="{ filterModalOpen: false, monthDropdownOpen: false }">
    <div class="w-full mx-auto">
        <!-- Header - Compact Single Row -->
        <div class="mb-6 animate-fade-in-up" style="position: relative; z-index: 10;">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Title and Subtitle -->
                <div class="flex-1 min-w-[300px]">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                        MTTR & MTBF Analysis
                    </h1>
                    <p class="text-sm text-gray-600">
                        Mean Time To Repair & Mean Time Between Failures
                    </p>
                </div>
                
                <!-- Date, Filter, Period Info, and Data Source - All in one row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Info - Display current data period -->
                    <div class="text-sm text-gray-700 bg-gray-100 px-3 py-1.5 rounded border border-gray-300 whitespace-nowrap">
                        <span class="font-semibold text-gray-800">Period:</span> 
                        <span class="text-gray-700">{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}</span> | 
                        <span class="font-semibold text-gray-800">Days:</span> 
                        <span class="text-gray-700">{{ $daysInMonth }} days</span>
                    </div>
                    
                    <!-- Date Filters -->
                    <form method="GET" action="{{ route('mttr_mtbf.index') }}" class="flex items-center gap-2" id="filterForm">
                        <!-- Month Button -->
                        <div class="relative" style="z-index: 9999;">
                            <button type="button" @click="monthDropdownOpen = !monthDropdownOpen" 
                                    class="bg-white border border-gray-300 rounded px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 relative z-10">
                                {{ \Carbon\Carbon::create(null, $selectedMonth, 1)->format('F') }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="monthDropdownOpen" 
                                 @click.away="monthDropdownOpen = false"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 style="z-index: 99999; position: absolute;"
                                 class="mt-1 w-36 bg-white rounded-md shadow-2xl border-2 border-gray-300 max-h-60 overflow-y-auto">
                                <button type="button" 
                                        onclick="document.getElementById('monthInput').value='all'; document.getElementById('filterForm').submit();"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 {{ $selectedMonth == 'all' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                                    All Months
                                </button>
                                @for($m = 1; $m <= 12; $m++)
                                    <button type="button" 
                                            onclick="document.getElementById('monthInput').value='{{ $m }}'; document.getElementById('filterForm').submit();"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 {{ $selectedMonth == $m ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                                        {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="month" id="monthInput" value="{{ $selectedMonth }}">
                        </div>
                        
                        <!-- Year Input -->
                        <input type="text" 
                               name="year" 
                               id="year" 
                               value="{{ $selectedYear }}"
                               onchange="document.getElementById('filterForm').submit();"
                               class="w-16 border border-gray-300 rounded px-2 py-1.5 text-sm font-medium bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                        
                        <!-- Preserve existing filters -->
                        @if(request('plant'))
                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                        @endif
                        @if(request('process'))
                            <input type="hidden" name="process" value="{{ request('process') }}">
                        @endif
                        @if(request('line'))
                            <input type="hidden" name="line" value="{{ request('line') }}">
                        @endif
                        @if(request('room'))
                            <input type="hidden" name="room" value="{{ request('room') }}">
                        @endif
                        @if(request('typeMachine'))
                            <input type="hidden" name="typeMachine" value="{{ request('typeMachine') }}">
                        @endif
                        @if(request('machine'))
                            <input type="hidden" name="machine" value="{{ request('machine') }}">
                        @endif
                        @if(request('data_source'))
                            <input type="hidden" name="data_source" value="{{ request('data_source') }}">
                        @endif
                    </form>
                    
                    <!-- Filter Button (Modal) -->
                    <button type="button" 
                            @click="filterModalOpen = true" 
                            class="relative bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1.5 px-3 rounded shadow transition flex items-center gap-1.5 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                        @if(request()->hasAny(['plant', 'process', 'line', 'room', 'typeMachine', 'machine']))
                            <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>
                    
                    <!-- Data Source -->
                    <div class="flex items-center gap-2">
                        <label for="data_source" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Data Source:</label>
                        <form method="GET" action="{{ route('mttr_mtbf.index') }}" class="inline-block" id="dataSourceForm">
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            @foreach(request()->except(['data_source', 'month', 'year']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="data_source" id="data_source" 
                                    onchange="document.getElementById('dataSourceForm').submit();"
                                    class="px-3 py-1.5 border border-gray-300 rounded shadow-sm bg-white text-sm text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                                <option value="downtime" {{ ($dataSource ?? 'downtime') === 'downtime' ? 'selected' : '' }}>Downtime</option>
                                <option value="downtime_erp" {{ ($dataSource ?? 'downtime') === 'downtime_erp' ? 'selected' : '' }}>Downtime ERP</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" style="position: relative; z-index: 1;">
            <!-- Overall MTTR -->
            <div class="stat-card stat-card-1 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-100 hover:shadow-2xl" style="opacity: 1 !important; display: block !important;">
                <div class="flex items-center">
                    
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Overall MTTR</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($overallMTTR, 2) }}</p>
                        <p class="text-xs text-white mt-2">minutes</p>
                        <p class="text-xs text-white/80 mt-2 italic">Total Duration ÷ Total Failures</p>
                    </div>
                    
                </div>
            </div>

            <div class="stat-card stat-card-4 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-200 hover:shadow-2xl" style="opacity: 1 !important; display: block !important;">
                <div class="flex items-center">
                    
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Overall MTBF</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($overallMTBF, 0) }}</p>
                        <p class="text-xs text-white mt-2">minutes</p>
                        <p class="text-xs text-white/80 mt-2 italic">Operating Time ÷ Total Failures</p>
                    </div>
                    
                </div>
            </div>

            <div class="stat-card stat-card-2 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300 hover:shadow-2xl" style="opacity: 1 !important; display: block !important;">
                <div class="flex items-center">
                    
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Total Failures</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($overallDowntimeCount) }}</p>
                        <p class="text-xs text-white mt-2">failures</p>
                    </div>
                    
                </div>
            </div>

            <div class="stat-card stat-card-5 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300 hover:shadow-2xl" style="opacity: 1 !important; display: block !important;">
                <div class="flex items-center">
                    
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Total Downtime</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($overallDowntimeDuration, 0) }}</p>
                        <p class="text-xs text-white mt-2">minutes</p>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Top 10 MTTR & MTBF Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top 10 MTTR Chart -->
            <div class="chart-card rounded-xl shadow-lg p-6 h-[600px] flex flex-col animate-fade-in-up delay-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Top 10 MTTR (Highest)</h2>
                </div>
                @if($top10MTTR->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="top10MTTRChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Top 10 MTBF Chart -->
            <div class="chart-card rounded-xl shadow-lg p-6 h-[600px] flex flex-col animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Top 10 MTBF (Highest)</h2>
                </div>
                @if($top10MTBF->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="top10MTBFChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- MTTR & MTBF Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- MTTR Table -->
            <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        MTTR (Mean Time To Repair)
                    </h2>
                    <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold">
                        Higher = Slower Repair
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed; width: 100%;">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">No</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">ID Machine</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Plant</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Line</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">MTTR (min)</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Count</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Total (min)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($mttrPaginated as $index => $item)
                            <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition-colors">
                                <td class="px-3 py-3 text-sm text-gray-900">{{ ($mttrCurrentPage - 1) * $perPage + $loop->iteration }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item->idMachine ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item->typeMachine ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item->plant ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item->line ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm font-bold text-right text-blue-600">{{ number_format($item->mttr ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700">{{ number_format($item->downtime_count ?? 0) }}</td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700">{{ number_format($item->total_duration ?? 0, 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">No MTTR data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- MTTR Pagination -->
                @if($mttrTotalPages > 1)
                <div class="mt-4 flex justify-center items-center gap-2">
                    @if($mttrCurrentPage > 1)
                        <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mttr_page' => $mttrCurrentPage - 1])) }}" 
                           class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Previous</a>
                    @endif
                    
                    @for($i = 1; $i <= $mttrTotalPages; $i++)
                        @if($i == 1 || $i == $mttrTotalPages || ($i >= $mttrCurrentPage - 2 && $i <= $mttrCurrentPage + 2))
                            <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mttr_page' => $i])) }}" 
                               class="px-3 py-2 {{ $i == $mttrCurrentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded hover:bg-blue-600 hover:text-white transition">
                                {{ $i }}
                            </a>
                        @elseif($i == $mttrCurrentPage - 3 || $i == $mttrCurrentPage + 3)
                            <span class="px-3 py-2 text-gray-500">...</span>
                        @endif
                    @endfor
                    
                    @if($mttrCurrentPage < $mttrTotalPages)
                        <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mttr_page' => $mttrCurrentPage + 1])) }}" 
                           class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Next</a>
                    @endif
                </div>
                @endif
            </div>

            <!-- MTBF Table -->
            <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        MTBF (Mean Time Between Failures)
                    </h2>
                    <span class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">
                        Higher = More Reliable
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed; width: 100%;">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">No</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">ID Machine</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Type</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Plant</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Line</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">MTBF (min)</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Failures</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Downtime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($mtbfPaginated as $index => $item)
                            <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-green-50 transition-colors">
                                <td class="px-3 py-3 text-sm text-gray-900">{{ ($mtbfCurrentPage - 1) * $perPage + $loop->iteration }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item['idMachine'] ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item['typeMachine'] ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item['plant'] ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $item['line'] ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm font-bold text-right text-green-600">{{ number_format($item['mtbf'] ?? 0, 0) }}</td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700">{{ number_format($item['failure_count'] ?? 0) }}</td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700">{{ number_format($item['total_duration'] ?? 0, 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">No MTBF data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- MTBF Pagination -->
                @if($mtbfTotalPages > 1)
                <div class="mt-4 flex justify-center items-center gap-2">
                    @if($mtbfCurrentPage > 1)
                        <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mtbf_page' => $mtbfCurrentPage - 1])) }}" 
                           class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Previous</a>
                    @endif
                    
                    @for($i = 1; $i <= $mtbfTotalPages; $i++)
                        @if($i == 1 || $i == $mtbfTotalPages || ($i >= $mtbfCurrentPage - 2 && $i <= $mtbfCurrentPage + 2))
                            <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mtbf_page' => $i])) }}" 
                               class="px-3 py-2 {{ $i == $mtbfCurrentPage ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded hover:bg-green-600 hover:text-white transition">
                                {{ $i }}
                            </a>
                        @elseif($i == $mtbfCurrentPage - 3 || $i == $mtbfCurrentPage + 3)
                            <span class="px-3 py-2 text-gray-500">...</span>
                        @endif
                    @endfor
                    
                    @if($mtbfCurrentPage < $mtbfTotalPages)
                        <a href="{{ route('mttr_mtbf.index', array_merge(request()->query(), ['mtbf_page' => $mtbfCurrentPage + 1])) }}" 
                           class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Next</a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Information Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- MTTR Explanation -->
            <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About MTTR
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    <strong>MTTR (Mean Time To Repair)</strong> measures the average time required to repair a failed machine.
                </p>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Formula:</strong> MTTR = Total Downtime Duration / Number of Failures
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Lower is better:</strong> A lower MTTR indicates faster repair times and better maintenance efficiency.
                </p>
            </div>

            <!-- MTBF Explanation -->
            <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About MTBF
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    <strong>MTBF (Mean Time Between Failures)</strong> measures the average time between failures of a machine.
                </p>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Formula:</strong> MTBF = Operating Time / Number of Failures
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Higher is better:</strong> A higher MTBF indicates better machine reliability and less frequent failures.
                </p>
            </div>

            <!-- Calculation Info -->
            <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Calculation Details
                </h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Available Time:</strong> {{ number_format($totalAvailableMinutes, 0) }} minutes ({{ $daysInMonth }} days × 24 hours)</p>
                    <p><strong>Total Downtime:</strong> {{ number_format($overallDowntimeDuration, 0) }} minutes</p>
                    <p><strong>Operating Time:</strong> {{ number_format($overallOperatingTime, 0) }} minutes</p>
                    <p><strong>Total Failures:</strong> {{ number_format($overallDowntimeCount) }} breakdowns</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div x-show="filterModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="filterModalOpen = false"
         @keydown.escape.window="filterModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="filterModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6">
                <!-- Close Button -->
                <button @click="filterModalOpen = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter Settings
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Set your filter criteria and click Save to apply</p>
                </div>
                
                <!-- Modal Content -->
                <form method="GET" action="{{ route('mttr_mtbf.index') }}" id="filterForm" class="space-y-4">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                        <!-- Plant -->
                        <div>
                            <label for="modal_plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                            <select name="plant" id="modal_plant" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Plants</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant }}" {{ request('plant') == $plant ? 'selected' : '' }}>{{ $plant }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Process -->
                        <div>
                            <label for="modal_process" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                            <select name="process" id="modal_process" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Processes</option>
                                @foreach($processes as $process)
                                    <option value="{{ $process }}" {{ request('process') == $process ? 'selected' : '' }}>{{ $process }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Line -->
                        <div>
                            <label for="modal_line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                            <select name="line" id="modal_line" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Lines</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line }}" {{ request('line') == $line ? 'selected' : '' }}>{{ $line }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Room -->
                        <div>
                            <label for="modal_room" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                            <select name="room" id="modal_room" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room }}" {{ request('room') == $room ? 'selected' : '' }}>{{ $room }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Type Machine -->
                        <div>
                            <label for="modal_typeMachine" class="block text-sm font-medium text-gray-700 mb-2">Type Machine</label>
                            <select name="typeMachine" id="modal_typeMachine" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Types</option>
                                @foreach($typeMachines as $typeMachine)
                                    <option value="{{ $typeMachine }}" {{ request('typeMachine') == $typeMachine ? 'selected' : '' }}>{{ $typeMachine }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Machine -->
                        <div>
                            <label for="modal_machine" class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                            <select name="machine" id="modal_machine" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Machines</option>
                                @foreach($machines as $machine)
                                    <option value="{{ $machine }}" {{ request('machine') == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                        <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                            Cancel
                        </button>
                        <a href="{{ route('mttr_mtbf.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
                            Reset Filter
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded text-sm transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Filter
                        </button>
                    </div>
                </form>
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

    // ========== TOP 10 MTTR (VERTICAL BAR CHART) ==========
    const top10MTTRCtx = document.getElementById('top10MTTRChart');
    if (top10MTTRCtx) {
        const top10MTTRData = @json($top10MTTR);
        if (top10MTTRData.length > 0) {
            const mttrLabels = top10MTTRData.map(m => m.idMachine);
            const mttrValues = top10MTTRData.map(m => parseFloat(m.mttr) || 0);
            const mttrTypes = top10MTTRData.map(m => m.typeMachine || 'N/A');
            const mttrCounts = top10MTTRData.map(m => m.downtime_count || 0);
            const mttrPlants = top10MTTRData.map(m => m.plant || 'N/A');
            const mttrLines = top10MTTRData.map(m => m.line || 'N/A');
            const mttrTotalDurations = top10MTTRData.map(m => parseFloat(m.total_duration) || 0);
            const mttrColors = ['#EF4444', '#F97316', '#FB923C', '#FBBF24', '#84CC16', '#10B981', '#06B6D4', '#3B82F6', '#8B5CF6', '#EC4899'];

            new Chart(top10MTTRCtx, {
                type: 'bar',
                data: {
                    labels: mttrLabels,
                    datasets: [{
                        label: 'MTTR (minutes)',
                        data: mttrValues,
                        backgroundColor: mttrColors,
                        borderColor: mttrColors,
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
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return mttrLabels[index] || 'N/A';
                                },
                                label: function(context) {
                                    const index = context[0].dataIndex;
                                    const value = context.parsed.y || 0;
                                    return [
                                        'Type Machine: ' + mttrTypes[index],
                                        'Plant: ' + mttrPlants[index],
                                        'Line: ' + mttrLines[index],
                                        'MTTR: ' + Math.round(value) + ' minutes',
                                        'Downtime Count: ' + mttrCounts[index] + 'x',
                                        'Total Duration: ' + Math.round(mttrTotalDurations[index]) + ' minutes'
                                    ];
                                }
                            }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'ID Machine',
                                font: { size: 12, weight: 'bold' },
                                padding: { top: 10 }
                            },
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MTTR (minutes)',
                                font: { size: 12, weight: 'bold' },
                                padding: { top: 3 }
                            },
                            ticks: {
                                callback: function(value) { return Math.round(value); },
                                font: { size: 10 },
                                stepSize: 5
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        }
                    },
                }
            });
        }
    }

    // ========== TOP 10 MTBF (VERTICAL BAR CHART) ==========
    const top10MTBFCtx = document.getElementById('top10MTBFChart');
    if (top10MTBFCtx) {
        const top10MTBFData = @json($top10MTBF);
        if (top10MTBFData.length > 0) {
            const mtbfLabels = top10MTBFData.map(m => m.idMachine || 'N/A');
            const mtbfValues = top10MTBFData.map(m => parseFloat(m.mtbf) || 0);
            const mtbfTypes = top10MTBFData.map(m => m.typeMachine || 'N/A');
            const mtbfCounts = top10MTBFData.map(m => m.failure_count || 0);
            const mtbfPlants = top10MTBFData.map(m => m.plant || 'N/A');
            const mtbfLines = top10MTBFData.map(m => m.line || 'N/A');
            const mtbfTotalDurations = top10MTBFData.map(m => parseFloat(m.total_duration) || 0);
            const mtbfOperatingTimes = top10MTBFData.map(m => parseFloat(m.operating_time) || 0);
            const mtbfColors = ['#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#3B82F6', '#8B5CF6', '#EC4899', '#F97316', '#FB923C', '#FBBF24'];

            new Chart(top10MTBFCtx, {
                type: 'bar',
                data: {
                    labels: mtbfLabels,
                    datasets: [{
                        label: 'MTBF (minutes)',
                        data: mtbfValues,
                        backgroundColor: mtbfColors,
                        borderColor: mtbfColors,
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
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return mtbfLabels[index] || 'N/A';
                                },
                                label: function(context) {
                                    const index = context[0].dataIndex;
                                    const value = context.parsed.y || 0;
                                    return [
                                        'Type Machine: ' + mtbfTypes[index],
                                        'Plant: ' + mtbfPlants[index],
                                        'Line: ' + mtbfLines[index],
                                        'MTBF: ' + Math.round(value) + ' minutes',
                                        'Failure Count: ' + mtbfCounts[index] + 'x',
                                        'Total Downtime: ' + Math.round(mtbfTotalDurations[index]) + ' minutes',
                                        'Operating Time: ' + Math.round(mtbfOperatingTimes[index]) + ' minutes'
                                    ];
                                }
                            }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'ID Machine',
                                font: { size: 12, weight: 'bold' },
                                padding: { top: 10 }
                            },
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MTBF (minutes)',
                                font: { size: 12, weight: 'bold' },
                                padding: { top: 3 }
                            },
                            ticks: {
                                callback: function(value) { return Math.round(value); },
                                font: { size: 10 },
                                stepSize: 1000
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        }
                    },
                }
            });
        }
    }
});
</script>
@endpush
@endsection

