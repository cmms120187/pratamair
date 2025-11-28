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
    [x-cloak] { display: none !important; }
    .skill-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin: 2px;
    }
    .skill-badge-high { background: #10B981; color: white; } /* Expert - Green */
    .skill-badge-advance { background: #3B82F6; color: white; } /* Advance - Blue */
    .skill-badge-medium { background: #F59E0B; color: white; } /* Intermediate - Orange */
    .skill-badge-low { background: #EF4444; color: white; } /* Beginner - Red */
    .skill-circle {
        width: 80px;
        height: 80px;
        position: relative;
    }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen" x-data="{ filterModalOpen: false, selectedMechanic: null, monthDropdownOpen: false }">
    <div class="w-full mx-auto">
        <!-- Header - Compact Single Row -->
        <div class="mb-6 animate-fade-in-up" style="position: relative; z-index: 10;">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Title and Subtitle -->
                <div class="flex-1 min-w-[300px]">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                        Laporan Kinerja Mekanik
                    </h1>
                    <p class="text-sm text-gray-600">
                        Informasi Jam Terbang, Jumlah Perbaikan, Rata-rata Waktu Perbaikan & Skill Matrix
                    </p>
                </div>
                
                <!-- Date, Filter, Period Info, and Data Source - All in one row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Info - Display current data period -->
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
                    <div class="text-sm text-gray-700 bg-gray-100 px-3 py-1.5 rounded border border-gray-300 whitespace-nowrap">
                        <span class="font-semibold text-gray-800">Period:</span> 
                        <span class="text-gray-700">{{ $periodText }}</span>
                    </div>
                    
                    <!-- Date Filters -->
                    <form method="GET" action="{{ route('mechanic_performance.index') }}" class="flex items-center gap-2" id="filterForm">
                        <!-- Month Button -->
                        <div class="relative" style="z-index: 9999;">
                            <button type="button" @click="monthDropdownOpen = !monthDropdownOpen" 
                                    class="bg-white border border-gray-300 rounded px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 relative z-10">
                                @if($selectedMonth == 'all')
                                    All Months
                                @else
                                    {{ \Carbon\Carbon::create(null, $selectedMonth, 1)->format('F') }}
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
                               placeholder="Year"
                               onchange="document.getElementById('filterForm').submit();"
                               class="w-20 border border-gray-300 rounded px-2 py-1.5 text-sm font-medium bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                        
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
                        @if(request()->hasAny(['plant', 'process', 'line', 'room', 'typeMachine']))
                            <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>
                    
                    <!-- Data Source -->
                    <div class="flex items-center gap-2">
                        <label for="data_source" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Data Source:</label>
                        <form method="GET" action="{{ route('mechanic_performance.index') }}" class="inline-block" id="dataSourceForm">
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
                    <form method="GET" action="{{ route('mechanic_performance.index') }}">
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

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card stat-card-1 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white mb-1">Total Mekanik</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ $mechanicStats->count() }}</p>
                    <p class="text-xs text-white mt-2">orang</p>
                </div>
            </div>

            <div class="stat-card stat-card-2 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white mb-1">Total Jam Terbang</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ number_format($mechanicStats->sum('total_duration') / 60, 1) }}</p>
                    <p class="text-xs text-white mt-2">jam</p>
                </div>
            </div>

            <div class="stat-card stat-card-3 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white mb-1">Total Perbaikan</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ number_format($mechanicStats->sum('total_repairs')) }}</p>
                    <p class="text-xs text-white mt-2">perbaikan</p>
                </div>
            </div>

            <div class="stat-card stat-card-4 rounded-xl shadow-xl p-6 text-white animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-full">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-white mb-1">Rata-rata Waktu Perbaikan</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ number_format($mechanicStats->avg('avg_duration'), 1) }}</p>
                    <p class="text-xs text-white mt-2">menit</p>
                </div>
            </div>
        </div>

        <!-- Mechanic Performance Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 animate-fade-in-up delay-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Daftar Kinerja Mekanik
                </h2>
                <div class="text-sm text-gray-600 bg-blue-50 px-4 py-2 rounded-full font-semibold">
                    Menampilkan {{ count($mechanicStats) }} mekanik
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID Mekanik</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nama Mekanik</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Jam Terbang (jam)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Jumlah Perbaikan</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Rata-rata Waktu (menit)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Min (menit)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Max (menit)</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mechanicStats as $stat)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition-all duration-200 hover:shadow-sm animate-fade-in-up" style="animation-delay: {{ $loop->index * 0.03 }}s; opacity: 0;">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">
                                <span class="px-3 py-1.5 rounded-full font-bold {{ $loop->iteration <= 3 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : ($loop->iteration <= 10 ? 'bg-gradient-to-r from-blue-400 to-blue-600 text-white shadow-md' : 'bg-gray-200 text-gray-700') }}">
                                    #{{ $loop->iteration }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900">{{ $stat->idMekanik }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $stat->nameMekanik }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 text-right font-bold">{{ number_format($stat->total_duration / 60, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right font-semibold">{{ number_format($stat->total_repairs) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-purple-600 text-right font-semibold">{{ number_format($stat->avg_duration, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">{{ number_format($stat->min_duration, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">{{ number_format($stat->max_duration, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <button @click="selectedMechanic = '{{ $stat->idMekanik }}'" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-4 py-1.5 rounded-lg text-xs font-semibold transition shadow-md hover:shadow-lg transform hover:scale-105">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Skill Matrix
                                    </span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-lg font-semibold text-gray-400">Tidak ada data mekanik untuk periode yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Skill Matrix Modal -->
        <div x-show="selectedMechanic"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="selectedMechanic = null"
             @keydown.escape.window="selectedMechanic = null"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-cloak
             style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            
            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="selectedMechanic"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <!-- Close Button -->
                    <button @click="selectedMechanic = null" 
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    
                    <!-- Modal Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Skill Matrix
                        </h2>
                        @foreach($mechanicStats as $stat)
                            <p class="text-sm text-gray-500 mt-1" x-show="selectedMechanic === '{{ $stat->idMekanik }}'" x-cloak>
                                Mekanik: {{ $stat->nameMekanik }} (ID: {{ $stat->idMekanik }})
                            </p>
                        @endforeach
                    </div>
                    
                    <!-- Skill Matrix Content -->
                    <div>
                        @foreach($mechanicStats as $stat)
                            <div x-show="selectedMechanic === '{{ $stat->idMekanik }}'" x-cloak>
                                @php
                                    $machines = $skillMatrix->get($stat->idMekanik) ?? collect();
                                @endphp
                                
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ $stat->nameMekanik }} (ID: {{ $stat->idMekanik }})</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @forelse($machines as $typeMachine)
                                            @php
                                                $repairCount = $typeMachine->repair_count;
                                                $avgDuration = $typeMachine->avg_duration;
                                                
                                                // Determine skill level based on repair count
                                                if ($repairCount >= 20) {
                                                    $skillLevel = 'expert';
                                                    $skillClass = 'skill-badge-high';
                                                    $skillLabel = 'Expert';
                                                    $skillPercent = 100;
                                                } elseif ($repairCount >= 10) {
                                                    $skillLevel = 'advance';
                                                    $skillClass = 'skill-badge-advance';
                                                    $skillLabel = 'Advance';
                                                    $skillPercent = 75;
                                                } elseif ($repairCount >= 5) {
                                                    $skillLevel = 'intermediate';
                                                    $skillClass = 'skill-badge-medium';
                                                    $skillLabel = 'Intermediate';
                                                    $skillPercent = 50;
                                                } else {
                                                    $skillLevel = 'beginner';
                                                    $skillClass = 'skill-badge-low';
                                                    $skillLabel = 'Beginner';
                                                    $skillPercent = 25;
                                                }
                                            @endphp
                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="font-semibold text-gray-800 text-lg">{{ $typeMachine->typeMachine }}</span>
                                                    <span class="skill-badge {{ $skillClass }}">
                                                        {{ $skillLabel }}
                                                    </span>
                                                </div>
                                                <div class="flex items-start justify-between gap-4">
                                                    <!-- Left: Type Machine Info -->
                                                    <div class="flex-1">
                                                        <p class="text-xs text-gray-500 mb-2">
                                                            <span class="font-medium">ID Mesin:</span>
                                                            @if(isset($typeMachine->machines_list) && count($typeMachine->machines_list) > 0)
                                                                <span class="text-gray-700">{{ implode(', ', $typeMachine->machines_list) }}</span>
                                                            @else
                                                                <span class="text-gray-500">-</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-xs text-gray-500">Jumlah Mesin: {{ $typeMachine->machine_count ?? 0 }} unit</p>
                                                        <p class="text-xs text-gray-500">Perbaikan: {{ $typeMachine->repair_count }}x</p>
                                                        <p class="text-xs text-gray-500">Rata-rata: {{ number_format($avgDuration, 1) }} menit</p>
                                                        <p class="text-xs text-gray-500">Total: {{ number_format($typeMachine->total_duration, 1) }} menit</p>
                                                    </div>
                                                    <!-- Right: Skill Circle Progress -->
                                                    <div class="skill-circle flex-shrink-0">
                                                        @php
                                                            $radius = 15;
                                                            $circumference = 2 * M_PI * $radius;
                                                            $dashOffset = $circumference - ($circumference * $skillPercent / 100);
                                                            // Expert: green, Advance: blue, Intermediate: orange, Beginner: red
                                                            if ($skillLevel == 'expert') {
                                                                $strokeColor = '#10B981'; // Green
                                                            } elseif ($skillLevel == 'advance') {
                                                                $strokeColor = '#3B82F6'; // Blue
                                                            } elseif ($skillLevel == 'intermediate') {
                                                                $strokeColor = '#F59E0B'; // Orange
                                                            } else {
                                                                $strokeColor = '#EF4444'; // Red
                                                            }
                                                        @endphp
                                                        <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                                                            <!-- Background circle -->
                                                            <circle cx="18" cy="18" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                                            <!-- Progress circle -->
                                                            <circle cx="18" cy="18" r="{{ $radius }}" fill="none" 
                                                                    stroke="{{ $strokeColor }}" 
                                                                    stroke-width="3"
                                                                    stroke-dasharray="{{ $circumference }}"
                                                                    stroke-dashoffset="{{ $dashOffset }}"
                                                                    stroke-linecap="round"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full">
                                                <p class="text-sm text-gray-500 text-center py-4">Belum ada history perbaikan mesin</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak
         style="display: none;">
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
                <form method="GET" action="{{ route('mechanic_performance.index') }}" id="filterForm" class="space-y-4">
                    <input type="hidden" name="month" value="{{ $selectedMonth == 'all' ? 'all' : $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear == 'all' ? 'all' : $selectedYear }}">
                    
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
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                        <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                            Cancel
                        </button>
                        <a href="{{ route('mechanic_performance.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
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
@endsection

