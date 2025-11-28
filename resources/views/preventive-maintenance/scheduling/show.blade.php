@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Schedule Details</h1>
                <p class="text-gray-600 mt-1">{{ $schedule->title }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('preventive-maintenance.scheduling.edit', $schedule->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Edit
                </a>
                <a href="{{ route('preventive-maintenance.scheduling.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Back
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Schedule Information -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Schedule Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->machine->idMachine ?? '-' }} - {{ $schedule->machine->machineType->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $schedule->machine->plant->name ?? '-' }} / {{ $schedule->machine->process->name ?? '-' }} / {{ $schedule->machine->line->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Maintenance Point</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->maintenancePoint->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Frequency</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ ucfirst($schedule->frequency_type) }}
                            @if($schedule->frequency_value > 1)
                                (Every {{ $schedule->frequency_value }})
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->start_date->format('Y-m-d') }}</p>
                    </div>
                    @if($schedule->end_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->end_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($schedule->status == 'active') bg-green-100 text-green-800
                            @elseif($schedule->status == 'completed') bg-blue-100 text-blue-800
                            @elseif($schedule->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($schedule->status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Assigned To</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->assignedUser->name ?? '-' }}</p>
                    </div>
                    @if($schedule->preferred_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Preferred Time</label>
                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($schedule->preferred_time)->format('H:i') }}</p>
                    </div>
                    @endif
                    @if($schedule->estimated_duration)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Estimated Duration</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->estimated_duration }} minutes</p>
                    </div>
                    @endif
                    @if($schedule->description)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                        <p class="text-sm text-gray-900">{{ $schedule->description }}</p>
                    </div>
                    @endif
                    @if($schedule->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                        <p class="text-sm text-gray-900">{{ $schedule->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Executions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Executions</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($schedule->executions as $execution)
                        <div class="border rounded p-3 hover:bg-gray-50">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $execution->scheduled_date->format('Y-m-d') }}</p>
                                    <p class="text-xs text-gray-500">Status: 
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($execution->status == 'completed') bg-green-100 text-green-800
                                            @elseif($execution->status == 'in_progress') bg-yellow-100 text-yellow-800
                                            @elseif($execution->status == 'pending') bg-gray-100 text-gray-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($execution->status) }}
                                        </span>
                                    </p>
                                    @if($execution->performedBy)
                                        <p class="text-xs text-gray-500 mt-1">By: {{ $execution->performedBy->name }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('preventive-maintenance.controlling.show', $execution->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No executions yet.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <a href="{{ route('preventive-maintenance.controlling.create') }}?schedule_id={{ $schedule->id }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center justify-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Execution
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

