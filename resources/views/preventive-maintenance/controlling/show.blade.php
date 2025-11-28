@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Execution Details</h1>
                <p class="text-gray-600 mt-1">{{ $execution->schedule->title ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('preventive-maintenance.controlling.edit', $execution->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Edit
                </a>
                <a href="{{ route('preventive-maintenance.controlling.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
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
                        <label class="block text-sm font-medium text-gray-500 mb-1">Schedule</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->schedule->title ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Scheduled Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->scheduled_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($execution->status == 'completed') bg-green-100 text-green-800
                            @elseif($execution->status == 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($execution->status == 'pending') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($execution->status) }}
                        </span>
                    </div>
                    @if($execution->actual_start_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual Start Time</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->actual_start_time->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @endif
                    @if($execution->actual_end_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Actual End Time</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->actual_end_time->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @endif
                    @if($execution->duration)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->duration }} minutes</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Performed By</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $execution->performedBy->name ?? '-' }}</p>
                    </div>
                    @if($execution->cost)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Cost</label>
                        <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($execution->cost, 0, ',', '.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Execution Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Execution Details</h2>
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
                    @if($execution->photo_before || $execution->photo_after)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Photos</label>
                        <div class="grid grid-cols-2 gap-4">
                            @if($execution->photo_before)
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Before</p>
                                    <img src="{{ Storage::url($execution->photo_before) }}" alt="Before" class="w-full h-32 object-cover rounded border cursor-pointer" onclick="openPhotoModal('{{ Storage::url($execution->photo_before) }}')">
                                </div>
                            @endif
                            @if($execution->photo_after)
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">After</p>
                                    <img src="{{ Storage::url($execution->photo_after) }}" alt="After" class="w-full h-32 object-cover rounded border cursor-pointer" onclick="openPhotoModal('{{ Storage::url($execution->photo_after) }}')">
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-75" onclick="closePhotoModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Photo Preview</h3>
                <button onclick="closePhotoModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="text-center">
                <img id="photoModalImg" src="" alt="Photo" class="max-w-full max-h-96 mx-auto rounded">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPhotoModal(photoUrl) {
    document.getElementById('photoModalImg').src = photoUrl;
    document.getElementById('photoModal').classList.remove('hidden');
}

function closePhotoModal() {
    document.getElementById('photoModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});
</script>
@endpush
@endsection

