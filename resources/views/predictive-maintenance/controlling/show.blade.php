@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Execution Details</h1>
                <p class="text-gray-600 mt-1">{{ $execution->schedule->maintenancePoint->name ?? $execution->schedule->title ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('predictive-maintenance.controlling.edit', $execution->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Edit
                </a>
                <a href="{{ route('predictive-maintenance.controlling.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Execution Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Execution Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Maintenance Point</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->schedule->maintenancePoint->name ?? $execution->schedule->title ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->schedule->machineErp->idMachine ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $execution->schedule->machineErp->machineType->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $execution->schedule->machineErp->plant_name ?? '-' }} /
                            {{ $execution->schedule->machineErp->line_name ?? '-' }}
                        </p>
                    </div>
                    @if($execution->schedule->standard)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Standard</label>
                        <p class="text-sm font-semibold text-blue-900">{{ $execution->schedule->standard->name ?? '-' }}</p>
                        <p class="text-xs text-blue-700">
                            Range: {{ $execution->schedule->standard->min_value ?? '-' }} - {{ $execution->schedule->standard->max_value ?? '-' }} {{ $execution->schedule->standard->unit ?? '' }}
                            @if($execution->schedule->standard->target_value !== null)
                                | Target: {{ $execution->schedule->standard->target_value }} {{ $execution->schedule->standard->unit ?? '' }}
                            @endif
                        </p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Scheduled Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->scheduled_date ? \Carbon\Carbon::parse($execution->scheduled_date)->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($execution->status == 'completed') bg-green-100 text-green-800
                            @elseif($execution->status == 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($execution->status == 'pending') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $execution->status)) }}
                        </span>
                    </div>
                    @if($execution->measured_value !== null)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Measured Value</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $execution->measured_value }} {{ $execution->schedule->standard->unit ?? '' }}
                            @if($execution->measurement_status)
                                <span class="ml-2 px-2 py-0.5 rounded text-xs
                                    @if($execution->measurement_status == 'normal') bg-green-100 text-green-800
                                    @elseif($execution->measurement_status == 'warning') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($execution->measurement_status) }}
                                </span>
                            @endif
                        </p>
                    </div>
                    @endif
                    @if($execution->actual_start_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual Start Time</label>
                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($execution->actual_start_time)->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($execution->actual_end_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual End Time</label>
                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($execution->actual_end_time)->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($execution->performedBy)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Performed By</label>
                        <p class="text-sm font-semibold text-gray-900">
                            @if($execution->performedBy->nik)
                                <span class="text-xs text-gray-400">{{ $execution->performedBy->nik }}</span><br>
                            @endif
                            {{ $execution->performedBy->name }}
                        </p>
                    </div>
                    @endif
                    @if($execution->cost)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Cost</label>
                        <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($execution->cost, 0, ',', '.') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h2>
                <div class="space-y-4">
                    @if($execution->findings)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Findings</label>
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $execution->findings }}</p>
                    </div>
                    @endif
                    @if($execution->actions_taken)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actions Taken</label>
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $execution->actions_taken }}</p>
                    </div>
                    @endif
                    @if($execution->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $execution->notes }}</p>
                    </div>
                    @endif
                    @if($execution->checklist)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Checklist</label>
                        @php
                            $checklistData = $execution->checklist ?? '[]';
                            $checklist = is_array($checklistData) ? $checklistData : json_decode($checklistData, true);
                            if (!is_array($checklist)) {
                                $checklist = [];
                            }
                        @endphp
                        @if(!empty($checklist))
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($checklist as $item)
                                    <li class="text-sm text-gray-900">
                                        <span class="{{ isset($item['checked']) && $item['checked'] ? 'line-through text-gray-500' : '' }}">
                                            {{ $item['item'] ?? '' }}
                                        </span>
                                        @if(isset($item['notes']) && $item['notes'])
                                            <span class="text-xs text-gray-500">({{ $item['notes'] }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Photos -->
        @if($execution->photo_before || $execution->photo_after)
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Photos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($execution->photo_before)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Photo Before</label>
                    <img src="{{ asset('storage/' . $execution->photo_before) }}" alt="Photo Before" class="w-full h-64 object-cover rounded border">
                </div>
                @endif
                @if($execution->photo_after)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Photo After</label>
                    <img src="{{ asset('storage/' . $execution->photo_after) }}" alt="Photo After" class="w-full h-64 object-cover rounded border">
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
