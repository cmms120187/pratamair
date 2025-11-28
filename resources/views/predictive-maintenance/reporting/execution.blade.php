@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Execution Report</h1>
                <p class="text-sm text-gray-500 mt-1">Predictive Maintenance Executions</p>
            </div>
            <a href="{{ route('predictive-maintenance.reporting.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('predictive-maintenance.reporting.execution') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                    <select name="machine_id" id="machine_id" class="w-full border rounded px-3 py-2">
                        <option value="">All Machines</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ $machineId == $machine->id ? 'selected' : '' }}>
                                {{ $machine->idMachine }} - {{ $machine->machineType->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full border rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="skipped" {{ $status == 'skipped' ? 'selected' : '' }}>Skipped</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end gap-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('predictive-maintenance.reporting.execution') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-gray-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Total Cost</p>
                <p class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($stats['total_cost'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Executions ({{ $executions->total() }})</h3>
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>

            @if($executions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Standard</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Measured Value</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Performed By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Scheduled Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($executions as $execution)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $execution->schedule->machine->machineType->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $execution->schedule->machine->room->plant->name ?? '-' }} /
                                        {{ $execution->schedule->machine->room->line->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($execution->schedule->standard)
                                        <div class="font-semibold">{{ $execution->schedule->standard->name }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ $execution->schedule->standard->min_value ?? '-' }} - {{ $execution->schedule->standard->max_value ?? '-' }} {{ $execution->schedule->standard->unit ?? '' }}
                                        </div>
                                    @else
                                        -
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
                                    @if($execution->status == 'completed')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                    @elseif($execution->status == 'in_progress')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                                    @elseif($execution->status == 'pending')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                    @elseif($execution->status == 'skipped')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Skipped</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                    @endif
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
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->scheduled_date ? \Carbon\Carbon::parse($execution->scheduled_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $execution->cost ? 'Rp ' . number_format($execution->cost, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $executions->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No executions found for the selected criteria.</p>
            @endif
        </div>
    </div>
</div>
@endsection
