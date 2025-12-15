@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Preventive Maintenance - Scheduling</h1>
            <div class="flex items-center gap-3">
                <button type="button" @click="filterModalOpen = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['period_type', 'period_month', 'period_year', 'plant', 'line', 'machine_type', 'search_id_machine']))
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <a href="{{ route('preventive-maintenance.scheduling.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create Schedule
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed; width: 100%;">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 40px;">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 110px;">ID Mesin</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 200px;">Nama Mesin</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Plant</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Line</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">PIC</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Status (%)</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Schedule</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($paginator as $data)
                    @php
                        $machine = $data['machine'];
                        $schedules = $data['schedules'];
                        $completionPercentage = $data['completion_percentage'] ?? 0;
                        $schedulesByDate = $data['schedules_by_date'] ?? [];
                    @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-3 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $loop->iteration + ($paginator->currentPage() - 1) * $paginator->perPage() }}
                        </td>
                        <td class="px-3 py-3 text-sm font-semibold text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $machine->idMachine ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $machine->machineType->name ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $machine->plant_name ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $machine->line_name ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $data['pic_name'] ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-full bg-gray-200 rounded-full h-6 mb-1 relative">
                                    <div class="h-6 rounded-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-300
                                        @if($completionPercentage >= 80) bg-green-500
                                        @elseif($completionPercentage >= 50) bg-yellow-500
                                        @else bg-red-500
                                        @endif" 
                                        style="width: {{ min($completionPercentage, 100) }}%">
                                        @if($completionPercentage > 0){{ $completionPercentage }}%@endif
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $data['completed_schedules'] ?? 0 }}/{{ $data['total_schedules'] ?? 0 }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $schedulesByDate = $data['schedules_by_date'] ?? [];
                                    $uniqueDates = array_keys($schedulesByDate);
                                    $machineId = $machine->id;
                                @endphp
                                @foreach($uniqueDates as $dateKey)
                                    @php
                                        $dateSchedules = $schedulesByDate[$dateKey];
                                        $firstSchedule = $dateSchedules[0];
                                        $dateObj = \Carbon\Carbon::parse($dateKey);
                                        
                                        // Determine overall status for this date
                                        $hasAnyExecution = false;
                                        $allCompleted = true;
                                        $hasOverdue = false;
                                        $hasCancelled = false;
                                        
                                        foreach ($dateSchedules as $sch) {
                                            $hasExec = $sch->executions()->exists();
                                            if ($hasExec) {
                                                $hasAnyExecution = true;
                                                $exec = $sch->executions()->latest()->first();
                                                if (!($exec && $exec->status == 'completed')) {
                                                    $allCompleted = false;
                                                }
                                            } else {
                                                $allCompleted = false;
                                                if ($sch->start_date < now()->toDateString() && $sch->status == 'active') {
                                                    $hasOverdue = true;
                                                }
                                            }
                                            
                                            if ($sch->status == 'cancelled' || $sch->status == 'inactive') {
                                                $hasCancelled = true;
                                            }
                                        }
                                        
                                        // Determine color based on overall status
                                        $bgColor = 'bg-gray-300';
                                        $textColor = 'text-gray-700';
                                        
                                        if ($hasCancelled) {
                                            $bgColor = 'bg-gray-300';
                                            $textColor = 'text-gray-700';
                                        } elseif ($allCompleted && $hasAnyExecution) {
                                            $bgColor = 'bg-green-500';
                                            $textColor = 'text-white';
                                        } elseif ($hasOverdue) {
                                            $bgColor = 'bg-red-500';
                                            $textColor = 'text-white';
                                        } elseif ($dateObj->isToday()) {
                                            $bgColor = 'bg-yellow-500';
                                            $textColor = 'text-white';
                                        } elseif ($dateObj < now()->toDateString()) {
                                            $bgColor = 'bg-orange-500';
                                            $textColor = 'text-white';
                                        } else {
                                            $bgColor = 'bg-blue-400';
                                            $textColor = 'text-white';
                                        }
                                    @endphp
                                    <button type="button" 
                                            onclick="showSchedulePoints('{{ $machineId }}', '{{ $dateKey }}')"
                                            class="px-2 py-1 text-xs rounded {{ $bgColor }} {{ $textColor }} cursor-pointer hover:opacity-80 transition" 
                                            title="Klik untuk melihat detail point maintenance">
                                        {{ $dateObj->format('d/m') }}
                                    </button>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <button type="button" 
                                        onclick="deleteAllSchedules({{ $machine->id }}, '{{ $machine->idMachine }}')" 
                                        class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" 
                                        title="Delete All Schedules">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">No schedules found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $paginator->links() }}
        </div>
        
        <!-- Legend -->
        <div class="mt-6 bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Legend:</h3>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-green-500 text-white">Date</span>
                    <span class="text-gray-600">Completed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-red-500 text-white">Date</span>
                    <span class="text-gray-600">Overdue</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-yellow-500 text-white">Date</span>
                    <span class="text-gray-600">Today</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-orange-500 text-white">Date</span>
                    <span class="text-gray-600">Past (Pending)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-blue-400 text-white">Date</span>
                    <span class="text-gray-600">Upcoming</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-gray-300 text-gray-700">Date</span>
                    <span class="text-gray-600">Cancelled/Inactive</span>
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
            <form method="GET" action="{{ route('preventive-maintenance.scheduling.index') }}" id="filterForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                    <!-- Period Type -->
                    <div>
                        <label for="modal_period_type" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <select name="period_type" id="modal_period_type" class="w-full border rounded px-3 py-2 text-sm" onchange="togglePeriodInputs()">
                            <option value="year" {{ request('period_type', 'year') == 'year' ? 'selected' : '' }}>Tahun</option>
                            <option value="month" {{ request('period_type') == 'month' ? 'selected' : '' }}>Bulan</option>
                        </select>
                    </div>
                    
                    <!-- Period Year -->
                    <div>
                        <label for="modal_period_year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select name="period_year" id="modal_period_year" class="w-full border rounded px-3 py-2 text-sm">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" {{ request('period_year', now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- Period Month (only show if period_type is month) -->
                    <div id="period_month_container" style="display: {{ request('period_type') == 'month' ? 'block' : 'none' }};">
                        <label for="modal_period_month" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <select name="period_month" id="modal_period_month" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="1" {{ request('period_month', now()->month) == 1 ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ request('period_month') == 2 ? 'selected' : '' }}>Februari</option>
                            <option value="3" {{ request('period_month') == 3 ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{ request('period_month') == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ request('period_month') == 5 ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ request('period_month') == 6 ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{ request('period_month') == 7 ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{ request('period_month') == 8 ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{ request('period_month') == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('period_month') == 10 ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ request('period_month') == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('period_month') == 12 ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    
                    <!-- Plant -->
                    <div>
                        <label for="modal_plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                        <select name="plant" id="modal_plant" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Plants</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ request('plant') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Line -->
                    <div>
                        <label for="modal_line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                        <select name="line" id="modal_line" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Lines</option>
                            @foreach($lines as $line)
                                <option value="{{ $line->id }}" {{ request('line') == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Machine Type -->
                    <div>
                        <label for="modal_machine_type" class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                        <select name="machine_type" id="modal_machine_type" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Machine Types</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ request('machine_type') == $machineType->id ? 'selected' : '' }}>{{ $machineType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Search ID Machine -->
                    <div>
                        <label for="modal_search_id_machine" class="block text-sm font-medium text-gray-700 mb-2">Search ID Mesin</label>
                        <input type="text" name="search_id_machine" id="modal_search_id_machine" value="{{ request('search_id_machine') }}" placeholder="Masukkan ID Mesin" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                    <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                        Cancel
                    </button>
                    <a href="{{ route('preventive-maintenance.scheduling.index') }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
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

<!-- Schedule Points Modal -->
<div id="schedulePointsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeScheduleModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full p-6 transform transition-all">
            <button onclick="closeScheduleModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-xl font-bold text-gray-800 mb-4" id="modalDateTitle">Maintenance Points</h3>
            
            <!-- Reschedule Section (only show if all pending) -->
            <div id="rescheduleSection" class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200 hidden">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="newScheduleDate" class="text-sm font-medium text-gray-700">Pindah Jadwal ke:</label>
                        <input type="date" id="newScheduleDate" class="border rounded px-3 py-1 text-sm flex-1 max-w-xs">
                    </div>
                    <button type="button" onclick="rescheduleAll()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 px-4 rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Pindah Jadwal
                    </button>
                </div>
                <p class="text-xs text-gray-600 mt-2">Semua maintenance point pada tanggal ini masih pending, Anda dapat memindahkan jadwal ke tanggal lain.</p>
            </div>
            
            @if(auth()->user()->isAdmin())
            <!-- Update PIC Section (Admin Only) -->
            <div id="updatePicSection" class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200 hidden">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="newPicSelect" class="text-sm font-medium text-gray-700">Ubah PIC:</label>
                        <select id="newPicSelect" class="border rounded px-3 py-1 text-sm flex-1 max-w-xs">
                            <option value="">Pilih PIC (Team Member)</option>
                            @foreach(\App\Models\User::where('role', 'mekanik')->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="updatePic()" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-1 px-4 rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Update PIC
                    </button>
                </div>
                <p class="text-xs text-gray-600 mt-2">Hanya schedule yang belum completed yang akan diupdate.</p>
            </div>
            @endif
            
            <!-- Batch Update Section -->
            <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="selectAllPoints" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="selectAllPoints" class="text-sm font-medium text-gray-700">Select All</label>
                    </div>
                    <div class="flex items-center gap-2 flex-1">
                        <label for="batchStatus" class="text-sm font-medium text-gray-700">Update Status:</label>
                        <select id="batchStatus" class="border rounded px-3 py-1 text-sm flex-1 max-w-xs">
                            <option value="">Pilih Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="skipped">Skipped</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="button" onclick="batchUpdateStatus()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-4 rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Selected
                    </button>
                </div>
            </div>
            
            <div id="schedulePointsContent" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Points will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Store schedules data for JavaScript access
const schedulesData = @json($schedulesDataForJs ?? []);

// Store current machineId and dateKey for reschedule
let currentMachineId = null;
let currentDateKey = null;

function showSchedulePoints(machineId, dateKey) {
    currentMachineId = machineId;
    currentDateKey = dateKey;
    
    const key = machineId + '_' + dateKey;
    const points = schedulesData[machineId] ? schedulesData[machineId][key] : [];
    
    if (!points || points.length === 0) {
        alert('Tidak ada maintenance point untuk tanggal ini');
        return;
    }
    
    // Format date for display
    const dateObj = new Date(dateKey + 'T00:00:00');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('id-ID', options);
    
    document.getElementById('modalDateTitle').textContent = `Maintenance Points - ${formattedDate}`;
    
    const content = document.getElementById('schedulePointsContent');
    content.innerHTML = '';
    
    // Check if all points are pending (no execution or all executions are pending)
    let allPending = true;
    
    points.forEach((point, index) => {
        let statusBadge = '';
        let statusClass = '';
        
        // Check if this point is not pending (for reschedule check)
        // A point is NOT pending if:
        // 1. It has execution with status != 'pending'
        // 2. It is completed
        // 3. Schedule status is cancelled/inactive (but we still allow reschedule for these)
        if (point.has_execution && point.execution_status && point.execution_status !== 'pending') {
            allPending = false;
        }
        if (point.is_completed) {
            allPending = false;
        }
        
        if (point.status == 'cancelled' || point.status == 'inactive') {
            statusBadge = 'Cancelled';
            statusClass = 'bg-gray-100 text-gray-800';
        } else if (point.is_completed) {
            statusBadge = 'Completed';
            statusClass = 'bg-green-100 text-green-800';
        } else if (point.is_overdue) {
            statusBadge = 'Overdue';
            statusClass = 'bg-red-100 text-red-800';
        } else {
            statusBadge = 'Pending';
            statusClass = 'bg-yellow-100 text-yellow-800';
        }
        
        const frequencyText = point.frequency_type ? 
            `${point.frequency_type.charAt(0).toUpperCase() + point.frequency_type.slice(1)} (${point.frequency_value}x)` : 
            '-';
        
        const pointDiv = document.createElement('div');
        pointDiv.className = 'border rounded p-3 bg-white';
        pointDiv.dataset.scheduleId = point.schedule_id;
        pointDiv.dataset.executionId = point.execution_id || '';
        pointDiv.dataset.scheduledDate = dateKey;
        
        let photoHtml = '';
        if (point.photo_url) {
            photoHtml = `<img src="${point.photo_url}" alt="Photo" class="w-16 h-16 object-cover rounded border float-left mr-3">`;
        }
        
        pointDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <input type="checkbox" class="point-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1" 
                       data-schedule-id="${point.schedule_id}" 
                       data-execution-id="${point.execution_id || ''}"
                       data-scheduled-date="${dateKey}">
                ${photoHtml}
                <div class="flex-1 ${point.photo_url ? 'ml-0' : ''}">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-900">${point.maintenance_point_name || point.title}</p>
                        <span class="px-2 py-1 text-xs rounded-full ${statusClass}">${statusBadge}</span>
                    </div>
                    ${point.description ? `<p class="text-sm text-gray-600 mt-1">${point.description}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-2">Periode: ${frequencyText}</p>
                </div>
            </div>
            <div class="clear-both"></div>
        `;
        content.appendChild(pointDiv);
    });
    
    // Show/hide reschedule section based on allPending
    const rescheduleSection = document.getElementById('rescheduleSection');
    if (allPending && points.length > 0) {
        rescheduleSection.classList.remove('hidden');
        // Set default date to tomorrow
        const tomorrow = new Date(dateObj);
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('newScheduleDate').value = tomorrow.toISOString().split('T')[0];
    } else {
        rescheduleSection.classList.add('hidden');
    }
    
    // Show/hide update PIC section for admin (only if there are non-completed schedules)
    @if(auth()->user()->isAdmin())
    const updatePicSection = document.getElementById('updatePicSection');
    const hasNonCompleted = points.some(p => !p.is_completed);
    if (hasNonCompleted && points.length > 0) {
        updatePicSection.classList.remove('hidden');
    } else {
        updatePicSection.classList.add('hidden');
    }
    @endif
    
    // Show modal
    const modal = document.getElementById('schedulePointsModal');
    modal.style.display = 'block';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('schedulePointsModal');
        if (modal && modal.style.display === 'block') {
            closeScheduleModal();
        }
    }
});

function closeScheduleModal() {
    const modal = document.getElementById('schedulePointsModal');
    modal.style.display = 'none';
    // Reset checkboxes and batch status
    document.getElementById('selectAllPoints').checked = false;
    document.getElementById('batchStatus').value = '';
    const checkboxes = document.querySelectorAll('.point-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    // Reset reschedule section
    document.getElementById('rescheduleSection').classList.add('hidden');
    document.getElementById('newScheduleDate').value = '';
    currentMachineId = null;
    currentDateKey = null;
}

function rescheduleAll() {
    if (!currentMachineId || !currentDateKey) {
        alert('Terjadi kesalahan, silakan tutup dan buka kembali modal');
        return;
    }
    
    const newDate = document.getElementById('newScheduleDate').value;
    if (!newDate) {
        alert('Pilih tanggal baru terlebih dahulu');
        return;
    }
    
    // Parse dates
    const oldDate = new Date(currentDateKey + 'T00:00:00');
    const newDateObj = new Date(newDate + 'T00:00:00');
    
    if (newDateObj <= oldDate) {
        if (!confirm('Tanggal baru harus lebih besar dari tanggal lama. Apakah Anda yakin ingin melanjutkan?')) {
            return;
        }
    }
    
    if (!confirm(`Apakah Anda yakin ingin memindahkan semua jadwal dari ${oldDate.toLocaleDateString('id-ID')} ke ${newDateObj.toLocaleDateString('id-ID')}?`)) {
        return;
    }
    
    // Get all schedule IDs for this date
    const key = currentMachineId + '_' + currentDateKey;
    const points = schedulesData[currentMachineId] ? schedulesData[currentMachineId][key] : [];
    const scheduleIds = points.map(p => p.schedule_id);
    
    if (scheduleIds.length === 0) {
        alert('Tidak ada schedule yang dapat dipindahkan');
        return;
    }
    
    // Send AJAX request
    fetch('{{ route("preventive-maintenance.scheduling.reschedule") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            schedule_ids: scheduleIds,
            old_date: currentDateKey,
            new_date: newDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Berhasil memindahkan ${data.updated_count} jadwal ke tanggal ${newDateObj.toLocaleDateString('id-ID')}`);
            // Reload page to refresh data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal memindahkan jadwal'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memindahkan jadwal');
    });
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllPoints');
    const checkboxes = document.querySelectorAll('.point-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

function batchUpdateStatus() {
    const selectedStatus = document.getElementById('batchStatus').value;
    if (!selectedStatus) {
        alert('Pilih status terlebih dahulu');
        return;
    }
    
    const checkedBoxes = document.querySelectorAll('.point-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu maintenance point');
        return;
    }
    
    if (!confirm(`Apakah Anda yakin ingin mengupdate status ${checkedBoxes.length} maintenance point menjadi "${selectedStatus}"?`)) {
        return;
    }
    
    // Collect data
    const updates = [];
    checkedBoxes.forEach(checkbox => {
        updates.push({
            schedule_id: checkbox.dataset.scheduleId,
            execution_id: checkbox.dataset.executionId || null,
            scheduled_date: checkbox.dataset.scheduledDate
        });
    });
    
    // Send AJAX request
    fetch('{{ route("preventive-maintenance.scheduling.batch-update-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            updates: updates,
            status: selectedStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Berhasil mengupdate ${data.updated_count} maintenance point`);
            // Reload page to refresh data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal mengupdate status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
}

// Update PIC function (Admin only)
function updatePic() {
    if (!currentMachineId || !currentDateKey) {
        alert('Tidak ada data yang dipilih');
        return;
    }
    
    const newPicId = document.getElementById('newPicSelect').value;
    if (!newPicId) {
        alert('Pilih PIC terlebih dahulu');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin mengubah PIC untuk semua schedule pada tanggal ini yang belum completed?')) {
        return;
    }
    
    fetch('{{ route("preventive-maintenance.scheduling.update-pic") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            machine_erp_id: currentMachineId,
            scheduled_date: currentDateKey,
            assigned_to: newPicId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal mengupdate PIC'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate PIC');
    });
}

function togglePeriodInputs() {
    const periodType = document.getElementById('modal_period_type').value;
    const periodMonthContainer = document.getElementById('period_month_container');
    
    if (periodType === 'month') {
        periodMonthContainer.style.display = 'block';
    } else {
        periodMonthContainer.style.display = 'none';
    }
}

function deleteAllSchedules(machineId, machineName) {
    if (!confirm(`Apakah Anda yakin ingin menghapus semua schedule untuk mesin ${machineName}?`)) {
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('preventive-maintenance/scheduling/delete-by-machine') }}/${machineId}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method spoofing
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
