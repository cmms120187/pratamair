@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Preventive Maintenance - Controlling</h1>
                <p class="text-sm text-gray-500 mt-1">Tanggal Hari Ini: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Filter Section -->
                <form method="GET" action="{{ route('preventive-maintenance.controlling.index') }}" class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <label for="month" class="text-sm font-medium text-gray-700">Bulan:</label>
                        <select name="month" id="month" class="border rounded px-2 py-1.5 text-sm">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $filterMonth == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="year" class="text-sm font-medium text-gray-700">Tahun:</label>
                        <select name="year" id="year" class="border rounded px-2 py-1.5 text-sm">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-1.5 px-3 rounded shadow transition flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                </form>
                <a href="{{ route('preventive-maintenance.controlling.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create Execution
                </a>
            </div>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Plan</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['plan'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Overdue</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

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
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 250px;">Tanggal Schedule</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($paginator as $data)
                    @php
                        $machine = $data['machine'];
                        $completionPercentage = $data['completion_percentage'] ?? 0;
                        $scheduleDates = $data['schedule_dates'] ?? [];
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
                            {{ $machine->plant->name ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $machine->line->name ?? '-' }}
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
                                    {{ $data['completed_jadwal'] ?? 0 }}/{{ $data['total_jadwal'] ?? 0 }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">
                            @if(!empty($scheduleDates))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($scheduleDates as $date)
                                        @php
                                            $dateObj = \Carbon\Carbon::parse($date);
                                            $isToday = $dateObj->isToday();
                                            $isPast = $dateObj->isPast() && !$isToday;
                                            $isFuture = $dateObj->isFuture();
                                            
                                            // Cek status completion untuk schedule dengan start_date = tanggal ini
                                            // (tidak peduli execution dilakukan di tanggal berapa)
                                            $hasCompletedExecution = false;
                                            $hasPartialCompletion = false;
                                            if ($isPast) {
                                                // Cari semua schedule dengan start_date = tanggal ini untuk machine ini
                                                $schedulesForDate = \App\Models\PreventiveMaintenanceSchedule::where('machine_id', $machine->id)
                                                    ->where('start_date', $date)
                                                    ->where('status', 'active')
                                                    ->get();
                                                
                                                if ($schedulesForDate->count() > 0) {
                                                    $completedCount = 0;
                                                    $totalSchedules = $schedulesForDate->count();
                                                    
                                                    foreach ($schedulesForDate as $schedule) {
                                                        // Cek apakah ada execution dengan status completed untuk schedule ini
                                                        $hasCompleted = $schedule->executions()
                                                            ->where('status', 'completed')
                                                            ->exists();
                                                        if ($hasCompleted) {
                                                            $completedCount++;
                                                        }
                                                    }
                                                    
                                                    // Semua completed
                                                    if ($completedCount == $totalSchedules) {
                                                        $hasCompletedExecution = true;
                                                    }
                                                    // Sebagian completed (belum semua)
                                                    elseif ($completedCount > 0 && $completedCount < $totalSchedules) {
                                                        $hasPartialCompletion = true;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <a href="{{ route('preventive-maintenance.controlling.create', [
                                            'machine_id' => $machine->id,
                                            'type_machine_id' => $machine->type_id,
                                            'scheduled_date' => $date
                                        ]) }}" 
                                           class="inline-flex items-center px-2 py-1 rounded text-xs font-medium transition hover:opacity-80 cursor-pointer
                                            @if($isToday) bg-yellow-100 text-yellow-800 border border-yellow-300 hover:bg-yellow-200
                                            @elseif($isPast && $hasCompletedExecution) bg-yellow-100 text-yellow-800 border border-yellow-300 hover:bg-yellow-200
                                            @elseif($isPast && $hasPartialCompletion) bg-orange-100 text-orange-800 border border-orange-300 hover:bg-orange-200
                                            @elseif($isPast && !$hasCompletedExecution && !$hasPartialCompletion) bg-red-100 text-red-800 border border-red-300 hover:bg-red-200
                                            @else bg-green-100 text-green-800 border border-green-300 hover:bg-green-200
                                            @endif"
                                           title="Klik untuk membuat execution pada tanggal {{ $dateObj->format('d/m/Y') }}@if($isPast && $hasCompletedExecution) - Sudah dikerjakan semua (terlewat)@elseif($isPast && $hasPartialCompletion) - Sebagian sudah dikerjakan (terlewat)@elseif($isPast && !$hasCompletedExecution && !$hasPartialCompletion) - Belum dikerjakan (terlewat)@endif">
                                            {{ $dateObj->format('d/m') }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('preventive-maintenance.controlling.create', ['machine_id' => $machine->id, 'type_machine_id' => $machine->type_id]) }}" 
                                   class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" 
                                   title="Create Execution">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">No machines with schedules found.</td>
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
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Legend Warna Tanggal:</h3>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-300">Date</span>
                    <span class="text-gray-600">Hari Ini</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-300">Date</span>
                    <span class="text-gray-600">Terlewat - Sudah Dikerjakan Semua</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-orange-100 text-orange-800 border border-orange-300">Date</span>
                    <span class="text-gray-600">Terlewat - Sebagian Sudah Dikerjakan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-red-100 text-red-800 border border-red-300">Date</span>
                    <span class="text-gray-600">Terlewat - Belum Dikerjakan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-green-100 text-green-800 border border-green-300">Date</span>
                    <span class="text-gray-600">Jadwal Mendatang</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
