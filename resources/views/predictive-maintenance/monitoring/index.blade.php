@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Predictive Maintenance - Monitoring</h1>
                <p class="text-sm text-gray-500 mt-1">Tanggal Hari Ini: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Filter Section -->
                <form method="GET" action="{{ route('predictive-maintenance.monitoring.index') }}" class="flex items-center gap-2">
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
                        <p class="text-sm text-gray-500">Total Schedules</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_schedules'] ?? 0 }}</p>
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
                        <p class="text-sm text-gray-500">Scheduled Machine</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['total_mesin_terjadwal'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
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
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
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
                        <p class="text-sm text-gray-500">Overdue</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Overview per Today</h3>
                <div class="h-64">
                    <canvas id="statusTodayChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Overview per Month</h3>
                <div class="h-64">
                    <canvas id="statusMonthChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Completion Rate</h3>
                <div class="h-64 flex items-center justify-center">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-blue-600">{{ $monthlyData['completion_rate'] ?? 0 }}%</p>
                        <p class="text-sm text-gray-500 mt-2">{{ $monthlyData['completed'] ?? 0 }} / {{ $monthlyData['total'] ?? 0 }} Jadwal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Schedules -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Schedules</h3>
            @if($upcomingSchedules->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Days Until</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Assigned To</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($upcomingSchedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $schedule->machine->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $schedule->machine->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $schedule->start_date ? \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-green-600 font-semibold">
                                    {{ \Carbon\Carbon::parse($schedule->start_date)->diffInDays(now()) }} days
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($schedule->assignedUser)
                                        @if($schedule->assignedUser->nik)
                                            <span class="text-xs text-gray-400">{{ $schedule->assignedUser->nik }}</span><br>
                                        @endif
                                        {{ $schedule->assignedUser->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('predictive-maintenance.controlling.create', [
                                        'machine_id' => $schedule->machine_id,
                                        'type_machine_id' => $schedule->machine->machine_type_id,
                                        'scheduled_date' => $schedule->start_date->format('Y-m-d')
                                    ]) }}"
                                       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No upcoming schedules.</p>
            @endif
        </div>

        <!-- Today's Executions -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Today's Executions</h3>
            @if($todayExecutions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Standard</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Measured Value</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Performed By</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($todayExecutions as $execution)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $execution->schedule->machine->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->schedule->standard->name ?? '-' }}
                                    @if($execution->schedule->standard)
                                        <div class="text-xs text-gray-400">
                                            {{ $execution->schedule->standard->min_value ?? '-' }} - {{ $execution->schedule->standard->max_value ?? '-' }} {{ $execution->schedule->standard->unit ?? '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($execution->measured_value !== null)
                                        <span class="font-semibold">{{ $execution->measured_value }}</span>
                                        <span class="text-gray-500">{{ $execution->schedule->standard->unit ?? '' }}</span>
                                        @if($execution->measurement_status)
                                            <span class="ml-2 px-2 py-0.5 rounded text-xs
                                                @if($execution->measurement_status == 'normal') bg-green-100 text-green-800
                                                @elseif($execution->measurement_status == 'warning') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($execution->measurement_status) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($execution->status == 'completed') bg-green-100 text-green-800
                                        @elseif($execution->status == 'in_progress') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $execution->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($execution->performedBy)
                                        @if($execution->performedBy->nik)
                                            <span class="text-xs text-gray-400">{{ $execution->performedBy->nik }}</span><br>
                                        @endif
                                        {{ $execution->performedBy->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('predictive-maintenance.controlling.show', $execution->id) }}"
                                       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No executions scheduled for today.</p>
            @endif
        </div>

        <!-- In Progress Executions -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">In Progress Executions</h3>
            @if($inProgressExecutions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-yellow-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Standard</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Start Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Performed By</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($inProgressExecutions as $execution)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $execution->schedule->machine->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->schedule->standard->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->actual_start_time ? \Carbon\Carbon::parse($execution->actual_start_time)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($execution->performedBy)
                                        @if($execution->performedBy->nik)
                                            <span class="text-xs text-gray-400">{{ $execution->performedBy->nik }}</span><br>
                                        @endif
                                        {{ $execution->performedBy->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('predictive-maintenance.updating.edit', $execution->id) }}"
                                       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No executions in progress.</p>
            @endif
        </div>

        <!-- Overdue Executions -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Overdue Executions</h3>
            @if($overdueExecutions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-red-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Days Overdue</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Assigned To</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($overdueExecutions as $execution)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $execution->schedule->machine->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->scheduled_date ? \Carbon\Carbon::parse($execution->scheduled_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-red-600 font-semibold">
                                    {{ \Carbon\Carbon::parse($execution->scheduled_date)->diffInDays(now()) }} days
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($execution->schedule->assignedUser)
                                        @if($execution->schedule->assignedUser->nik)
                                            <span class="text-xs text-gray-400">{{ $execution->schedule->assignedUser->nik }}</span><br>
                                        @endif
                                        {{ $execution->schedule->assignedUser->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('predictive-maintenance.controlling.create', [
                                        'machine_id' => $execution->schedule->machine_id,
                                        'type_machine_id' => $execution->schedule->machine->machine_type_id,
                                        'scheduled_date' => $execution->scheduled_date
                                    ]) }}"
                                       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No overdue executions.</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Overview per Today Chart
    const statusTodayCtx = document.getElementById('statusTodayChart');
    if (statusTodayCtx) {
        const statusToday = @json($statusToday ?? []);
        const totalToday = statusToday.total || 0;
        const completedToday = statusToday.completed || 0;
        const inProgressToday = statusToday.in_progress || 0;
        const pendingToday = statusToday.pending || 0;
        const overdueToday = statusToday.overdue || 0;

        if (totalToday > 0) {
            new Chart(statusTodayCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending', 'Overdue'],
                    datasets: [{
                        data: [completedToday, inProgressToday, pendingToday, overdueToday],
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)',
                            'rgb(239, 68, 68)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = totalToday || 1;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            const parent = statusTodayCtx.parentElement;
            parent.innerHTML = '<p class="text-gray-500 text-center py-8">Tidak ada jadwal sampai hari ini</p>';
        }
    }

    // Status Overview per Month Chart
    const statusMonthCtx = document.getElementById('statusMonthChart');
    if (statusMonthCtx) {
        const statusMonth = @json($statusMonth ?? []);
        const totalMonth = statusMonth.total || 0;
        const completedMonth = parseInt(statusMonth.completed || 0);
        const inProgressMonth = parseInt(statusMonth.in_progress || 0);
        const pendingMonth = parseInt(statusMonth.pending || 0);
        const overdueMonth = parseInt(statusMonth.overdue || 0);

        new Chart(statusMonthCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Pending', 'Overdue'],
                datasets: [{
                    data: [completedMonth, inProgressMonth, pendingMonth, overdueMonth],
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(156, 163, 175)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = totalMonth || 1;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
