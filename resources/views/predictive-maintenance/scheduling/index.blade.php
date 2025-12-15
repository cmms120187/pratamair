@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Predictive Maintenance - Scheduling</h1>
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
                <a href="{{ route('predictive-maintenance.scheduling.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
                            {{ $machine->machineType->name ?? $machine->type_name ?? '-' }}
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
                                    {{ $data['completed_jadwal'] ?? 0 }}/{{ $data['total_jadwal'] ?? 0 }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $uniqueDates = array_keys($schedulesByDate);
                                    // Sort dates to ensure proper chronological order using Carbon
                                    usort($uniqueDates, function($a, $b) {
                                        try {
                                            $dateA = \Carbon\Carbon::parse($a);
                                            $dateB = \Carbon\Carbon::parse($b);
                                            return $dateA->timestamp - $dateB->timestamp;
                                        } catch (\Exception $e) {
                                            // Fallback to string comparison if parsing fails
                                            return strcmp($a, $b);
                                        }
                                    });
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
                                @php
                                    $typeMachineId = $machine->machine_type_id ?? null;
                                    $firstDate = !empty($uniqueDates) ? $uniqueDates[0] : null;
                                @endphp
                                @if($firstDate)
                                <a href="{{ route('predictive-maintenance.controlling.create', ['type_machine_id' => $typeMachineId, 'machine_id' => $machine->id, 'scheduled_date' => $firstDate]) }}"
                                   class="inline-flex items-center justify-center bg-yellow-600 hover:bg-yellow-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" 
                                   title="Execute">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">No schedules found. <a href="{{ route('predictive-maintenance.scheduling.create') }}" class="text-blue-600 hover:underline">Create a new schedule</a></td>
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
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
         @click.away="filterModalOpen = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full" @click.stop>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Filter Schedules</h3>
                        <button type="button" @click="filterModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="GET" action="{{ route('predictive-maintenance.scheduling.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Period Type</label>
                                <select name="period_type" class="w-full border rounded px-3 py-2">
                                    <option value="year" {{ $periodType == 'year' ? 'selected' : '' }}>Year</option>
                                    <option value="month" {{ $periodType == 'month' ? 'selected' : '' }}>Month</option>
                                </select>
                            </div>
                            @if($periodType == 'month')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                <select name="period_month" class="w-full border rounded px-3 py-2">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $periodMonth == $i ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                <select name="period_year" class="w-full border rounded px-3 py-2">
                                    @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                        <option value="{{ $y }}" {{ $periodYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                                <select name="plant" class="w-full border rounded px-3 py-2">
                                    <option value="">All Plants</option>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant->id }}" {{ $plantId == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                                <select name="line" class="w-full border rounded px-3 py-2">
                                    <option value="">All Lines</option>
                                    @foreach($lines as $line)
                                        <option value="{{ $line->id }}" {{ $lineId == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                                <select name="machine_type" class="w-full border rounded px-3 py-2">
                                    <option value="">All Machine Types</option>
                                    @foreach($machineTypes as $type)
                                        <option value="{{ $type->id }}" {{ $machineTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search ID Machine</label>
                                <input type="text" name="search_id_machine" value="{{ $searchIdMachine }}" placeholder="ID Machine..." class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('predictive-maintenance.scheduling.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                                Reset
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
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
            
            <!-- Update PIC Section -->
            <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center gap-3 flex-wrap mb-3">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="updatePicSelect" class="text-sm font-medium text-gray-700 whitespace-nowrap">Ganti PIC:</label>
                        <select id="updatePicSelect" class="border rounded px-3 py-1 text-sm flex-1 max-w-xs">
                            <option value="">Pilih Team Leader</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                    @if($user->nik)
                                        ({{ $user->nik }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="updatePicButton" onclick="updatePic(event)" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-4 rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update PIC Terpilih
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <button type="button" onclick="selectAllSchedules()" class="text-xs text-blue-600 hover:text-blue-800 underline">Pilih Semua</button>
                    <span class="text-xs text-gray-500">|</span>
                    <button type="button" onclick="deselectAllSchedules()" class="text-xs text-blue-600 hover:text-blue-800 underline">Batal Pilih Semua</button>
                </div>
                <p class="text-xs text-gray-600">Centang jadwal yang ingin diupdate PIC-nya, lalu pilih Team Leader dan klik "Update PIC Terpilih".</p>
            </div>
            
            <!-- Reschedule Section -->
            <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center gap-3 flex-wrap mb-3">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="rescheduleDateInput" class="text-sm font-medium text-gray-700 whitespace-nowrap">Pindah Tanggal:</label>
                        <input type="date" id="rescheduleDateInput" class="border rounded px-3 py-1 text-sm flex-1 max-w-xs">
                    </div>
                    <button type="button" id="rescheduleButton" onclick="rescheduleSchedules(event)" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 px-4 rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Pindah Tanggal Terpilih
                    </button>
                </div>
                <p class="text-xs text-gray-600">Centang jadwal yang ingin dipindah, pilih tanggal baru, lalu klik "Pindah Tanggal Terpilih". Hanya jadwal yang belum dikerjakan yang bisa dipindah.</p>
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

// Store current machineId and dateKey for update PIC
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
    
    // Get current PIC from first point (all points should have same PIC)
    const currentPicId = points[0]?.assigned_to || '';
    const picSelect = document.getElementById('updatePicSelect');
    if (picSelect) {
        picSelect.value = currentPicId;
    }
    
    const content = document.getElementById('schedulePointsContent');
    content.innerHTML = '';
    
    points.forEach((point, index) => {
        let statusBadge = '';
        let statusClass = '';
        
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
        
        // Check if schedule can be rescheduled (only if not completed/updated)
        const canReschedule = !point.is_completed && (!point.has_execution || point.execution_status === 'pending');
        const checkboxDisabled = point.is_completed || (point.has_execution && point.execution_status !== 'pending');
        
        const pointDiv = document.createElement('div');
        pointDiv.className = `border rounded p-3 bg-white ${checkboxDisabled ? 'opacity-60' : ''}`;
        
        pointDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                    <input type="checkbox" 
                           class="schedule-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                           value="${point.schedule_id}" 
                           data-schedule-id="${point.schedule_id}"
                           data-can-reschedule="${canReschedule}"
                           ${checkboxDisabled ? 'disabled title="Jadwal ini sudah dikerjakan, tidak bisa dipindah"' : 'checked'}>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-900">${point.maintenance_point_name || '-'}</p>
                        <span class="px-2 py-1 text-xs rounded-full ${statusClass}">${statusBadge}</span>
                        ${checkboxDisabled ? '<span class="text-xs text-gray-500 italic">(Sudah dikerjakan)</span>' : ''}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Standard: ${point.standard_name || '-'}</p>
                    <p class="text-xs text-gray-500 mt-1">Range: ${point.standard_min || '-'} - ${point.standard_max || '-'} ${point.standard_unit || ''}</p>
                    ${point.description ? `<p class="text-xs text-gray-400 mt-2">${point.description}</p>` : ''}
                </div>
            </div>
        `;
        content.appendChild(pointDiv);
    });
    
    // Show modal
    const modal = document.getElementById('schedulePointsModal');
    modal.style.display = 'block';
}

function selectAllSchedules() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllSchedules() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

function updatePic(event) {
    // Get selected schedule IDs
    const checkboxes = document.querySelectorAll('.schedule-checkbox:checked');
    const selectedScheduleIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    if (selectedScheduleIds.length === 0) {
        alert('Pilih minimal satu jadwal yang ingin diupdate PIC-nya.');
        return;
    }
    
    const picSelect = document.getElementById('updatePicSelect');
    const selectedPicId = picSelect.value;
    
    // Show confirmation
    if (!confirm(`Apakah Anda yakin ingin mengubah PIC untuk ${selectedScheduleIds.length} jadwal yang dipilih?`)) {
        return;
    }
    
    // Disable button during request
    const updateButton = document.getElementById('updatePicButton');
    const originalText = updateButton.innerHTML;
    updateButton.disabled = true;
    updateButton.innerHTML = '<span class="animate-spin">⏳</span> Updating...';
    
    // Send AJAX request
    fetch('{{ route("predictive-maintenance.scheduling.update-pic") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            schedule_ids: selectedScheduleIds,
            assigned_to: selectedPicId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengupdate PIC');
            updateButton.disabled = false;
            updateButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi error saat mengupdate PIC');
        updateButton.disabled = false;
        updateButton.innerHTML = originalText;
    });
}

function rescheduleSchedules(event) {
    // Get selected schedule IDs - only those that can be rescheduled
    const checkboxes = document.querySelectorAll('.schedule-checkbox:checked:not([disabled])');
    const selectedScheduleIds = Array.from(checkboxes)
        .filter(cb => cb.dataset.canReschedule === 'true')
        .map(cb => parseInt(cb.value));
    
    if (selectedScheduleIds.length === 0) {
        // Check if any were selected but disabled
        const disabledChecked = document.querySelectorAll('.schedule-checkbox:checked[disabled]');
        if (disabledChecked.length > 0) {
            alert('Jadwal yang sudah dikerjakan (sudah diupdate) tidak bisa dipindah. Pilih jadwal yang belum dikerjakan.');
        } else {
            alert('Pilih minimal satu jadwal yang ingin dipindah tanggalnya (hanya jadwal yang belum dikerjakan).');
        }
        return;
    }
    
    const dateInput = document.getElementById('rescheduleDateInput');
    const newDate = dateInput.value;
    
    if (!newDate) {
        alert('Pilih tanggal baru untuk jadwal yang dipilih.');
        return;
    }
    
    // Show confirmation
    if (!confirm(`Apakah Anda yakin ingin memindahkan ${selectedScheduleIds.length} jadwal yang dipilih ke tanggal ${newDate}?\n\nCatatan: Hanya jadwal yang belum dikerjakan (belum diupdate) yang bisa dipindah.`)) {
        return;
    }
    
    // Disable button during request
    const rescheduleButton = document.getElementById('rescheduleButton');
    const originalText = rescheduleButton.innerHTML;
    rescheduleButton.disabled = true;
    rescheduleButton.innerHTML = '<span class="animate-spin">⏳</span> Memindahkan...';
    
    // Send AJAX request
    fetch('{{ route("predictive-maintenance.scheduling.reschedule") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            schedule_ids: selectedScheduleIds,
            new_date: newDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert(data.message || 'Gagal memindahkan jadwal');
            rescheduleButton.disabled = false;
            rescheduleButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi error saat memindahkan jadwal');
        rescheduleButton.disabled = false;
        rescheduleButton.innerHTML = originalText;
    });
}

function closeScheduleModal() {
    const modal = document.getElementById('schedulePointsModal');
    modal.style.display = 'none';
    currentMachineId = null;
    currentDateKey = null;
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
</script>
@endsection
