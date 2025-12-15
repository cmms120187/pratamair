@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Machine Detail</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('machines.edit', $machine->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Edit
                </a>
                <a href="{{ route('machines.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">ID Machine</label>
                        <div class="text-lg font-bold text-gray-900">{{ $machine->idMachine }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine Type</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-900">{{ $machine->machineType->name ?? '-' }}</span>
                            @if($machine->machineType)
                                <a href="{{ route('machine-types.show', $machine->machineType->id) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded shadow transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Maintenance Point
                                </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Group</label>
                        <div class="text-gray-900">
                            @if($machine->machineType && $machine->machineType->groupRelation)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $machine->machineType->groupRelation->name }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Systems</label>
                        <div class="text-gray-900">
                            @if($machine->machineType && $machine->machineType->systems->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($machine->machineType->systems as $system)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $system->nama_sistem }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Brand</label>
                        <div class="text-gray-900">{{ $machine->brand->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Model</label>
                        <div class="text-gray-900">{{ $machine->model->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Serial Number</label>
                        <div class="text-gray-900">{{ $machine->serial_number ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tahun Production</label>
                        <div class="text-gray-900">{{ $machine->tahun_production ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Document</label>
                        <div class="text-gray-900">{{ $machine->no_document ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Photo</label>
                        <div class="text-gray-900">
                            @if($machine->photo)
                                <img src="{{ Storage::url($machine->photo) }}" alt="Machine Photo" class="max-w-xs max-h-48 object-cover rounded border cursor-pointer" onclick="openPhotoModal('{{ Storage::url($machine->photo) }}')">
                            @else
                                <span class="text-gray-400">No photo available</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Location Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Plant</label>
                        <div class="text-gray-900">{{ $machine->plant->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Process</label>
                        <div class="text-gray-900">{{ $machine->process->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Line</label>
                        <div class="text-gray-900">{{ $machine->line->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Room</label>
                        <div class="text-gray-900">{{ $machine->room->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Timestamps</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                        <div class="text-gray-900">{{ $machine->created_at->format('d F Y H:i:s') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                        <div class="text-gray-900">{{ $machine->updated_at->format('d F Y H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="fixed inset-0 z-50 hidden overflow-hidden bg-black bg-opacity-90" onclick="closePhotoModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
            <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="overflow-auto w-full h-full flex items-center justify-center" id="photoModalContainer">
                <img id="photoModalImg" src="" alt="Photo" class="max-w-full max-h-full object-contain">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPhotoModal(photoUrl) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('photoModalImg');
    img.src = photoUrl;
    modal.classList.remove('hidden');
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});
</script>
@endpush
@endsection
