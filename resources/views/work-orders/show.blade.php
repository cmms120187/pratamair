@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Work Order Detail</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('work-orders.edit', $workOrder->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Edit
                </a>
                <a href="{{ route('work-orders.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-4 border-b">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">WO Number</label>
                    <div class="text-lg font-bold text-gray-900">{{ $workOrder->wo_number }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $workOrder->getStatusBadgeClass() }}">
                        {{ $workOrder->getStatusLabel() }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Priority</label>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $workOrder->getPriorityBadgeClass() }}">
                        {{ $workOrder->getPriorityLabel() }}
                    </span>
                </div>
            </div>

            <!-- Basic Information -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Order Date</label>
                        <div class="text-gray-900">{{ $workOrder->order_date->format('d F Y') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Due Date</label>
                        <div class="text-gray-900">{{ $workOrder->due_date ? $workOrder->due_date->format('d F Y') : '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine</label>
                        <div class="text-gray-900">
                            <div class="font-semibold">{{ $workOrder->machine->idMachine ?? '-' }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $workOrder->machine->machineType->name ?? '-' }} |
                                {{ ($workOrder->machine->room && $workOrder->machine->room->plant) ? $workOrder->machine->room->plant->name : '-' }} /
                                {{ ($workOrder->machine->room && $workOrder->machine->room->process) ? $workOrder->machine->room->process->name : '-' }} /
                                {{ ($workOrder->machine->room && $workOrder->machine->room->line) ? $workOrder->machine->room->line->name : '-' }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Assigned To</label>
                        <div class="text-gray-900">{{ $workOrder->assignedTo->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created By</label>
                        <div class="text-gray-900">{{ $workOrder->createdBy->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Description</h2>
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $workOrder->description }}</p>
                </div>
            </div>

            <!-- Problem Description -->
            @if($workOrder->problem_description)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Problem Description</h2>
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $workOrder->problem_description }}</p>
                </div>
            </div>
            @endif

            <!-- Solution -->
            @if($workOrder->solution)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Solution</h2>
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $workOrder->solution }}</p>
                </div>
            </div>
            @endif

            <!-- Cost & Duration -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Cost & Duration</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Estimated Cost</label>
                        <div class="text-gray-900">Rp {{ number_format($workOrder->estimated_cost ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual Cost</label>
                        <div class="text-gray-900">Rp {{ number_format($workOrder->actual_cost ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Estimated Duration</label>
                        <div class="text-gray-900">{{ $workOrder->estimated_duration_minutes ?? '-' }} minutes</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual Duration</label>
                        <div class="text-gray-900">{{ $workOrder->actual_duration_minutes ?? '-' }} minutes</div>
                    </div>
                </div>
            </div>

            <!-- Repair Timestamps -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Waktu Perbaikan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($workOrder->repair_started_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Mulai Perbaikan</label>
                        <div class="text-gray-900">{{ $workOrder->repair_started_at->format('d F Y H:i') }}</div>
                    </div>
                    @endif
                    @if($workOrder->repair_completed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Selesai Perbaikan</label>
                        <div class="text-gray-900">{{ $workOrder->repair_completed_at->format('d F Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Timestamps</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($workOrder->started_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Started At</label>
                        <div class="text-gray-900">{{ $workOrder->started_at->format('d F Y H:i:s') }}</div>
                    </div>
                    @endif
                    @if($workOrder->completed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Completed At</label>
                        <div class="text-gray-900">{{ $workOrder->completed_at->format('d F Y H:i:s') }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                        <div class="text-gray-900">{{ $workOrder->created_at->format('d F Y H:i:s') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                        <div class="text-gray-900">{{ $workOrder->updated_at->format('d F Y H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            @if($workOrder->photo_before || $workOrder->photo_after)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Photos</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($workOrder->photo_before)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Photo Before</label>
                        <img src="{{ asset('public-storage/' . $workOrder->photo_before) }}" alt="Photo Before" class="w-full rounded border-2 border-gray-300" onerror="this.style.display='none'">
                    </div>
                    @endif
                    @if($workOrder->photo_after)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Photo After</label>
                        <img src="{{ asset('public-storage/' . $workOrder->photo_after) }}" alt="Photo After" class="w-full rounded border-2 border-gray-300" onerror="this.style.display='none'">
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Sparepart yang Digunakan -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Sparepart yang Digunakan</h2>
                @if($workOrder->parts && $workOrder->parts->count() > 0)
                    <div class="space-y-2">
                        @foreach($workOrder->parts as $part)
                            <div class="flex items-center justify-between border rounded px-3 py-2 bg-gray-50">
                                <span class="text-gray-700">{{ $part->name }} ({{ $part->part_number ?? '-' }})</span>
                                <span class="text-gray-600 font-medium">Qty: {{ $part->pivot->quantity ?? 1 }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-50 rounded p-4">
                        <p class="text-gray-500 italic">Tidak ada sparepart yang digunakan</p>
                    </div>
                @endif
            </div>

            <!-- Notes -->
            @if($workOrder->notes)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Notes</h2>
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $workOrder->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
