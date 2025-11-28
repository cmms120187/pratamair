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
    .delay-200 { animation-delay: 0.2s; opacity: 0; }
    .delay-300 { animation-delay: 0.3s; opacity: 0; }
    .delay-250 { animation-delay: 0.25s; opacity: 0; }
    .delay-400 { animation-delay: 0.4s; opacity: 0; }
    .animate-fade-in-up.delay-100 {
        pointer-events: auto !important;
    }
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .animate-slide-in-right {
        animation: slideInRight 0.8s ease-out forwards;
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
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .delay-350 { animation-delay: 0.35s; opacity: 0; }
    .animate-fade-in-up.delay-350 {
        pointer-events: auto !important;
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
                        Summary Downtime Analysis
                    </h1>
                    <p class="text-sm text-gray-600">
                        Analisa Downtime per Tanggal, Top 5 Mesin & Problem
                    </p>
                </div>
                
                <!-- Date, Filter, Period Info, and Data Source - All in one row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Info - Display current data period -->
                    @php
                        $daysInMonth = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->daysInMonth;
                    @endphp
                    <div class="text-sm text-gray-700 bg-gray-100 px-3 py-1.5 rounded border border-gray-300 whitespace-nowrap">
                        <span class="font-semibold text-gray-800">Period:</span> 
                        <span class="text-gray-700">{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}</span> | 
                        <span class="font-semibold text-gray-800">Days:</span> 
                        <span class="text-gray-700">{{ $daysInMonth }} days</span>
                    </div>
                    
                    <!-- Date Filters -->
                    <form method="GET" action="{{ route('summary_downtime.index') }}" class="flex items-center gap-2" id="filterForm">
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
                        <form method="GET" action="{{ route('summary_downtime.index') }}" class="inline-block" id="dataSourceForm">
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
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form method="GET" action="{{ route('summary_downtime.index') }}">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Tambahan</h3>
                                <button type="button" @click="filterModalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if(isset($plants) && $plants->count() > 0)
                                <div>
                                    <label for="plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                                    <select name="plant" id="plant" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Plants</option>
                                        @foreach($plants as $plant)
                                            <option value="{{ $plant }}" {{ request('plant') == $plant ? 'selected' : '' }}>{{ $plant }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if(isset($processes) && $processes->count() > 0)
                                <div>
                                    <label for="process" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                                    <select name="process" id="process" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Processes</option>
                                        @foreach($processes as $process)
                                            <option value="{{ $process }}" {{ request('process') == $process ? 'selected' : '' }}>{{ $process }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if(isset($lines) && $lines->count() > 0)
                                <div>
                                    <label for="line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                                    <select name="line" id="line" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Lines</option>
                                        @foreach($lines as $line)
                                            <option value="{{ $line }}" {{ request('line') == $line ? 'selected' : '' }}>{{ $line }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if(isset($rooms) && $rooms->count() > 0)
                                <div>
                                    <label for="room" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                                    <select name="room" id="room" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Rooms</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room }}" {{ request('room') == $room ? 'selected' : '' }}>{{ $room }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if(isset($typeMachines) && $typeMachines->count() > 0)
                                <div>
                                    <label for="typeMachine" class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                                    <select name="typeMachine" id="typeMachine" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Machine Types</option>
                                        @foreach($typeMachines as $typeMachine)
                                            <option value="{{ $typeMachine }}" {{ request('typeMachine') == $typeMachine ? 'selected' : '' }}>{{ $typeMachine }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if(isset($machines) && $machines->count() > 0)
                                <div>
                                    <label for="machine" class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                                    <select name="machine" id="machine" class="w-full border rounded px-3 py-2 text-sm">
                                        <option value="">All Machines</option>
                                        @foreach($machines as $machine)
                                            <option value="{{ $machine }}" {{ request('machine') == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
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

        <!-- 1. Bar Chart: Downtime by Plant (1 kolom) - Paling Atas -->
        <div class="mb-8">
            <div class="chart-card rounded-xl shadow-lg p-6 flex flex-col animate-fade-in-up delay-200" style="height: 500px;">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 bg-clip-text text-transparent">Downtime by Plant</h2>
                    @if(request('plant'))
                        <button onclick="clearPlantFilter()" class="text-sm bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Filter: {{ request('plant') }}
                        </button>
                    @endif
                </div>
                @if($downtimeByPlant->count() > 0)
                <div class="flex-1 flex items-center justify-center" style="min-height: 0; height: calc(100% - 60px);">
                    <canvas id="downtimeByPlantChart" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- 2. Line Chart: Downtime per Tanggal dengan Baseline (1 kolom) -->
        <div class="mb-8">
            <div class="chart-card rounded-xl shadow-lg p-6 flex flex-col animate-fade-in-up delay-250" style="height: 500px;">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Downtime per Tanggal</h2>
                </div>
                @if($downtimePerDate->count() > 0)
                <div class="flex-1 flex items-center justify-center" style="min-height: 0; height: calc(100% - 60px);">
                    <canvas id="downtimePerDateChart" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- 3. Pie Charts: Top 5 ID Mesin & Top 5 Problem (2 kolom horizontal) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top 5 ID Mesin -->
            <div class="chart-card rounded-xl shadow-lg p-6 h-[500px] flex flex-col animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent">Top 5 ID Mesin Downtime Tertinggi</h2>
                </div>
                @if($top5Machines->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="top5MachinesChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>

            <!-- Top 5 Problem -->
            <div class="chart-card rounded-xl shadow-lg p-6 h-[500px] flex flex-col animate-fade-in-up delay-400">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Top 5 Problem Downtime Tertinggi</h2>
                </div>
                @if($top5Problems->count() > 0)
                <div class="flex-1 flex items-center justify-center min-h-0">
                    <canvas id="top5ProblemsChart"></canvas>
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No data available</p>
                @endif
            </div>
        </div>

        <!-- 4. Informasi Downtime Tertinggi (1 kolom) -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Longest Single Downtime -->
                <div class="stat-card-2 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300 hover:shadow-2xl">
                    <div class="flex items-center mb-4">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold">Downtime Terpanjang</h3>
                    </div>
                    @if($longestDowntime)
                    <div class="space-y-3">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                            <p class="text-sm text-white/80 mb-1">ID Mesin</p>
                            <p class="text-lg font-bold">{{ $longestDowntime->idMachine ?? 'N/A' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-white/80 mb-1">Type</p>
                                <p class="font-semibold">{{ $longestDowntime->typeMachine ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-white/80 mb-1">Tanggal</p>
                                <p class="font-semibold">{{ \Carbon\Carbon::parse($longestDowntime->date)->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <p class="text-sm text-white/80 mb-1">Duration</p>
                            <p class="text-2xl font-bold">{{ number_format((float)$longestDowntime->duration, 2) }} min</p>
                        </div>
                        <div>
                            <p class="text-sm text-white/80 mb-1">Problem</p>
                            <p class="font-semibold">{{ $longestDowntime->problemDowntime ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-white/80">No data available</p>
                    @endif
                </div>

                <!-- Highest Machine Downtime -->
                <div class="stat-card-1 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-350 hover:shadow-2xl">
                    <div class="flex items-center mb-4">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full mr-4 flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold">Mesin dengan Total Downtime Tertinggi</h3>
                    </div>
                    @if($highestMachineDowntime)
                    <div class="space-y-3">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                            <p class="text-sm text-white/80 mb-1">ID Mesin</p>
                            <p class="text-lg font-bold">{{ $highestMachineDowntime->idMachine ?? 'N/A' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-white/80 mb-1">Type</p>
                                <p class="font-semibold">{{ $highestMachineDowntime->typeMachine ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-white/80 mb-1">Plant</p>
                                <p class="font-semibold">{{ $highestMachineDowntime->plant ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <p class="text-sm text-white/80 mb-1">Total Duration</p>
                            <p class="text-2xl font-bold">{{ number_format($highestMachineDowntime->total_duration, 2) }} min</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-white/80 mb-1">Line</p>
                                <p class="font-semibold">{{ $highestMachineDowntime->line ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-white/80 mb-1">Count</p>
                                <p class="text-lg font-bold">{{ $highestMachineDowntime->downtime_count ?? 0 }}x</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-white/80">No data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== LINE CHART: Downtime per Tanggal dengan Baseline ==========
    const downtimePerDateCtx = document.getElementById('downtimePerDateChart');
    if (downtimePerDateCtx) {
        const downtimePerDateData = @json($downtimePerDate);
        const baselineAverage = {{ $baselineAverage }};
        
        if (downtimePerDateData.length > 0) {
            const dates = downtimePerDateData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
            });
            const durations = downtimePerDateData.map(d => parseFloat(d.total_duration) || 0);
            
            // Create baseline array (same value for all dates)
            const baselineData = dates.map(() => baselineAverage);
            
            new Chart(downtimePerDateCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Downtime per Hari',
                            data: durations,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Baseline (Rata-rata Bulan Sebelumnya)',
                            data: baselineData,
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + Math.round(context.parsed.y) + ' minutes';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal',
                                font: { size: 12, weight: 'bold' }
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (minutes)',
                                font: { size: 12, weight: 'bold' }
                            },
                            ticks: {
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            },
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' }
                        }
                    }
                }
            });
        }
    }

    // Function to clear plant filter
    function clearPlantFilter() {
        const url = new URL(window.location.href);
        url.searchParams.delete('plant');
        // Preserve all other parameters (month, year, and other filters)
        window.location.href = url.toString();
    }

    // ========== BAR CHART: Downtime by Plant ==========
    const downtimeByPlantCtx = document.getElementById('downtimeByPlantChart');
    if (downtimeByPlantCtx) {
        const downtimeByPlantData = @json($downtimeByPlant);
        if (downtimeByPlantData.length > 0) {
            const plantLabels = downtimeByPlantData.map(p => p.plant);
            const plantDurations = downtimeByPlantData.map(p => parseFloat(p.total_duration) || 0);
            const plantCounts = downtimeByPlantData.map(p => p.downtime_count || 0);
            
            // Color palette - vibrant and colorful
            const colorPalettes = [
                { start: '#667eea', end: '#764ba2' }, // Purple to violet
                { start: '#f093fb', end: '#f5576c' }, // Pink to red
                { start: '#4facfe', end: '#00f2fe' }, // Blue to cyan
                { start: '#43e97b', end: '#38f9d7' }, // Green to teal
                { start: '#fa709a', end: '#fee140' }, // Pink to yellow
                { start: '#30cfd0', end: '#330867' }, // Cyan to purple
                { start: '#a8edea', end: '#fed6e3' }, // Light cyan to pink
                { start: '#ff9a9e', end: '#fecfef' }, // Pink to light pink
                { start: '#ffecd2', end: '#fcb69f' }, // Peach to orange
                { start: '#ff8a80', end: '#ea4c89' }, // Red to pink
            ];
            
            const chartInstance = new Chart(downtimeByPlantCtx, {
                type: 'bar',
                data: {
                    labels: plantLabels,
                    datasets: [{
                        label: 'Total Downtime (minutes)',
                        data: plantDurations,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return '#667eea';
                            }
                            const index = context.dataIndex;
                            const palette = colorPalettes[index % colorPalettes.length];
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                            gradient.addColorStop(0, palette.start);
                            gradient.addColorStop(1, palette.end);
                            return gradient;
                        },
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutBounce',
                        delay: function(context) {
                            return context.dataIndex * 100;
                        }
                    },
                    onClick: function(event, elements) {
                        if (elements.length > 0) {
                            const element = elements[0];
                            const plantName = plantLabels[element.index];
                            
                            // Build URL with plant filter
                            const url = new URL(window.location.href);
                            url.searchParams.set('plant', plantName);
                            // Preserve all existing parameters (month, year, and other filters)
                            
                            // Reload page with filter
                            window.location.href = url.toString();
                        }
                    },
                    onHover: function(event, elements) {
                        event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                title: function(context) {
                                    return 'Plant: ' + context[0].label;
                                },
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const value = context.parsed.y;
                                    const count = plantCounts[index];
                                    const total = plantDurations.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return [
                                        'Total Duration: ' + Math.round(value) + ' minutes',
                                        'Downtime Count: ' + count + 'x',
                                        'Percentage: ' + percentage + '%'
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Plant',
                                font: { 
                                    size: 14, 
                                    weight: 'bold',
                                    color: '#4B5563'
                                },
                                padding: { top: 10 }
                            },
                            grid: { 
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: '600'
                                },
                                color: '#6B7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Duration (minutes)',
                                font: { 
                                    size: 14, 
                                    weight: 'bold',
                                    color: '#4B5563'
                                },
                                padding: { bottom: 10 }
                            },
                            ticks: {
                                callback: function(value) {
                                    return Math.round(value);
                                },
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: { 
                                display: true, 
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    }

    // ========== PIE CHART: Top 5 ID Mesin ==========
    const top5MachinesCtx = document.getElementById('top5MachinesChart');
    if (top5MachinesCtx) {
        const top5MachinesData = @json($top5Machines);
        if (top5MachinesData.length > 0) {
            const machineLabels = top5MachinesData.map(m => m.idMachine);
            const machineDurations = top5MachinesData.map(m => parseFloat(m.total_duration) || 0);
            const machineTypes = top5MachinesData.map(m => m.typeMachine || 'N/A');
            const machineCounts = top5MachinesData.map(m => m.downtime_count || 0);
            const machineColors = ['#EF4444', '#F97316', '#FB923C', '#FBBF24', '#84CC16'];
            
            const totalDuration = machineDurations.reduce((a, b) => a + b, 0);
            
            new Chart(top5MachinesCtx, {
                type: 'pie',
                data: {
                    labels: machineLabels,
                    datasets: [{
                        data: machineDurations,
                        backgroundColor: machineColors,
                        borderColor: '#ffffff',
                        borderWidth: 2
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
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function(context) {
                                    if (!context || !context.length || context[0] === undefined) return 'N/A';
                                    const index = context[0].dataIndex;
                                    return machineLabels[index] || 'N/A';
                                },
                                label: function(context) {
                                    if (!context || context.dataIndex === undefined) return '';
                                    const index = context.dataIndex;
                                    const value = context.parsed || 0;
                                    const percentage = totalDuration > 0 ? ((value / totalDuration) * 100).toFixed(1) : 0;
                                    return [
                                        'Type: ' + machineTypes[index],
                                        'Duration: ' + Math.round(value) + ' minutes',
                                        'Count: ' + machineCounts[index] + 'x',
                                        'Percentage: ' + percentage + '%'
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ========== PIE CHART: Top 5 Problem ==========
    const top5ProblemsCtx = document.getElementById('top5ProblemsChart');
    if (top5ProblemsCtx) {
        const top5ProblemsData = @json($top5Problems);
        if (top5ProblemsData.length > 0) {
            const problemLabels = top5ProblemsData.map(p => p.problemDowntime);
            const problemDurations = top5ProblemsData.map(p => parseFloat(p.total_duration) || 0);
            const problemCounts = top5ProblemsData.map(p => p.downtime_count || 0);
            const problemColors = ['#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#3B82F6'];
            
            const totalDuration = problemDurations.reduce((a, b) => a + b, 0);
            
            new Chart(top5ProblemsCtx, {
                type: 'pie',
                data: {
                    labels: problemLabels,
                    datasets: [{
                        data: problemDurations,
                        backgroundColor: problemColors,
                        borderColor: '#ffffff',
                        borderWidth: 2
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
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function(context) {
                                    if (!context || !context.length || context[0] === undefined) return 'N/A';
                                    const index = context[0].dataIndex;
                                    return problemLabels[index] || 'N/A';
                                },
                                label: function(context) {
                                    if (!context || context.dataIndex === undefined) return '';
                                    const index = context.dataIndex;
                                    const value = context.parsed || 0;
                                    const percentage = totalDuration > 0 ? ((value / totalDuration) * 100).toFixed(1) : 0;
                                    return [
                                        'Duration: ' + Math.round(value) + ' minutes',
                                        'Count: ' + problemCounts[index] + 'x',
                                        'Percentage: ' + percentage + '%'
                                    ];
                                }
                            }
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

