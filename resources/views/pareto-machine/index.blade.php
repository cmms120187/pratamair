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
    [x-cloak] { display: none !important; }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen" x-data="{ filterModalOpen: false, monthDropdownOpen: false }">
    <div class="w-full mx-auto">
        <!-- Header - Compact Single Row -->
        <div class="mb-6 animate-fade-in-up" style="position: relative; z-index: 10;">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Title and Subtitle -->
                <div class="flex-1 min-w-[300px]">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                        Laporan Pareto Mesin
                    </h1>
                    <p class="text-sm text-gray-600">
                        Analisis Pareto untuk Mesin dengan Downtime Tertinggi
                    </p>
                </div>
                
                <!-- Date, Filter, Period Info, and Data Source - All in one row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Info - Display current data period -->
                    @php
                        $periodText = '';
                        $daysText = '';
                        if ($selectedMonth != 'all' && $selectedYear != 'all') {
                            $periodText = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');
                            $daysText = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->daysInMonth . ' days';
                        } elseif ($selectedYear != 'all') {
                            $periodText = 'All Months ' . $selectedYear;
                            $daysText = 'Full Year';
                        } elseif ($selectedMonth != 'all') {
                            $periodText = \Carbon\Carbon::create()->month($selectedMonth)->format('F') . ' (All Years)';
                            $daysText = 'All Years';
                        } else {
                            $periodText = 'All Period';
                            $daysText = 'All Data';
                        }
                    @endphp
                    <div class="text-sm text-gray-700 bg-gray-100 px-3 py-1.5 rounded border border-gray-300 whitespace-nowrap">
                        <span class="font-semibold text-gray-800">Period:</span> 
                        <span class="text-gray-700">{{ $periodText }}</span> | 
                        <span class="font-semibold text-gray-800">Days:</span> 
                        <span class="text-gray-700">{{ $daysText }}</span>
                    </div>
                    
                    <!-- Date Filters -->
                    <form method="GET" action="{{ route('pareto-machine.index') }}" class="flex items-center gap-2" id="filterForm">
                        <!-- Month Button -->
                        <div class="relative" style="z-index: 9999;">
                            <button type="button" @click="monthDropdownOpen = !monthDropdownOpen" 
                                    class="bg-white border border-gray-300 rounded px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 relative z-10">
                                @if($selectedMonth == 'all')
                                    All Months
                                @else
                                    {{ \Carbon\Carbon::create()->month($selectedMonth)->locale('id')->isoFormat('MMMM') }}
                                @endif
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
                                    Semua Bulan
                                </button>
                                @foreach($months as $m)
                                    <button type="button" 
                                            onclick="document.getElementById('monthInput').value='{{ $m }}'; document.getElementById('filterForm').submit();"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 {{ $selectedMonth == $m ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                                        {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="month" id="monthInput" value="{{ $selectedMonth }}">
                        </div>
                        
                        <!-- Year Input -->
                        <div class="relative">
                            <input type="text" 
                                   name="year" 
                                   id="year" 
                                   value="{{ $selectedYear }}"
                                   placeholder="Year"
                                   onchange="document.getElementById('filterForm').submit();"
                                   class="w-20 border border-gray-300 rounded px-2 py-1.5 text-sm font-medium bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                        </div>
                        
                        <!-- Preserve existing filters -->
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
                    </button>
                    
                    <!-- Data Source -->
                    <div class="flex items-center gap-2">
                        <label for="data_source" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Data Source:</label>
                        <form method="GET" action="{{ route('pareto-machine.index') }}" class="inline-block" id="dataSourceForm">
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
        
        <!-- Filter Modal -->
        <div x-show="filterModalOpen" 
             @click.away="filterModalOpen = false"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="filterModalOpen = false"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="GET" action="{{ route('pareto-machine.index') }}">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Tambahan</h3>
                                <button type="button" @click="filterModalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500">Filter tambahan akan ditambahkan di sini jika diperlukan.</p>
                            </div>
                            
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            @if(request('data_source'))
                                <input type="hidden" name="data_source" value="{{ request('data_source') }}">
                            @endif
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Terapkan
                            </button>
                            <button type="button" @click="filterModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Mesin -->
            <div class="stat-card-1 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-100 hover:shadow-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Total Mesin</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ $stats['total_machines'] }}</p>
                        <p class="text-xs text-white mt-2">machines</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Downtime -->
            <div class="stat-card-2 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-150 hover:shadow-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Total Downtime</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($stats['total_downtime'], 0) }}</p>
                        <p class="text-xs text-white mt-2">minutes</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Frekuensi -->
            <div class="stat-card-3 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-200 hover:shadow-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Total Frekuensi</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($stats['total_frequency']) }}</p>
                        <p class="text-xs text-white mt-2">occurrences</p>
                    </div>
                </div>
            </div>
            
            <!-- Rata-rata per Mesin -->
            <div class="stat-card-4 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-250 hover:shadow-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Rata-rata per Mesin</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($stats['avg_downtime_per_machine'], 1) }}</p>
                        <p class="text-xs text-white mt-2">minutes</p>
                    </div>
                </div>
            </div>
            
            <!-- Top 20 (% Total) -->
            <div class="stat-card-5 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300 hover:shadow-2xl">
                <div class="flex items-center">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-white mb-1">Top 20 (% Total)</p>
                        <p class="text-4xl font-bold text-white mt-2">{{ number_format($stats['top_20_percentage'], 1) }}%</p>
                        <p class="text-xs text-white mt-2">of total downtime</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pareto Chart -->
        <div class="chart-card rounded-xl shadow-lg p-6 mb-6 h-[600px] flex flex-col animate-fade-in-up delay-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Chart Pareto Mesin</h2>
                <div class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    Top 20 Mesin
                </div>
            </div>
            <div class="relative flex-grow">
                <canvas id="paretoChart"></canvas>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up delay-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Data Pareto Mesin</h2>
                <div class="text-sm text-gray-600 bg-blue-50 px-4 py-2 rounded-full font-semibold">
                    Menampilkan {{ count($paretoData) }} mesin
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID Mesin</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tipe Mesin</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Downtime (min)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Frekuensi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">% dari Total</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Kumulatif (min)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">% Kumulatif</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paretoData as $data)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition-all duration-200 hover:shadow-sm">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">
                                <span class="px-3 py-1.5 rounded-full font-bold {{ $data['rank'] <= 3 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : ($data['rank'] <= 10 ? 'bg-gradient-to-r from-blue-400 to-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-700') }}">
                                    #{{ $data['rank'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900">{{ $data['machine_id'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $data['machine_type'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-bold text-blue-600">{{ number_format($data['duration'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right font-semibold">{{ number_format($data['frequency']) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right font-semibold">{{ number_format($data['percentage'], 2) }}%</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right font-semibold">{{ number_format($data['cumulative_duration'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                <span class="px-3 py-1.5 rounded-full font-bold {{ $data['cumulative_percentage'] >= 80 ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg' : ($data['cumulative_percentage'] >= 50 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md' : 'bg-gradient-to-r from-green-400 to-green-600 text-white shadow-md') }}">
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
                                    <p class="text-lg font-semibold text-gray-400">Tidak ada data downtime untuk periode yang dipilih.</p>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paretoData = @json($paretoData);
    const top20Data = paretoData.slice(0, 20); // Show top 20 for better visualization
    
    const ctx = document.getElementById('paretoChart');
    if (!ctx) return;
    
    const labels = top20Data.map(d => d.machine_id);
    const durations = top20Data.map(d => d.duration);
    const cumulativePercentages = top20Data.map(d => d.cumulative_percentage);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Downtime (menit)',
                    data: durations,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: '% Kumulatif',
                    data: cumulativePercentages,
                    type: 'line',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 2,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return `Downtime: ${context.parsed.y.toFixed(2)} menit`;
                            } else {
                                return `Kumulatif: ${context.parsed.y.toFixed(2)}%`;
                            }
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Pareto Chart - Top 20 Mesin dengan Downtime Tertinggi',
                    font: {
                        size: 18,
                        weight: 'bold'
                    },
                    color: '#4B5563',
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Downtime (menit)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Persentase Kumulatif (%)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            annotation: {
                annotations: {
                    line80: {
                        type: 'line',
                        yMin: 80,
                        yMax: 80,
                        borderColor: 'rgba(239, 68, 68, 0.5)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        label: {
                            content: '80%',
                            enabled: true,
                            position: 'end'
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection

