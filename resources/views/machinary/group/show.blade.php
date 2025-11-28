@extends('layouts.app')
@section('content')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Machine Type: {{ $machineType->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Maintenance Points</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('machine-types.edit', $machineType->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Edit
                </a>
                <a href="{{ route('machine-types.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Machine Type Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                    <div class="text-gray-900 font-semibold">{{ $machineType->name }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Model</label>
                    <div class="text-gray-900">{{ $machineType->model ?? '-' }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Group</label>
                    <div class="text-gray-900">
                        @if($machineType->groupRelation)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $machineType->groupRelation->name }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Systems</label>
                    <div class="text-gray-900">
                        @if($machineType->systems->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($machineType->systems as $system)
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
                    <div class="text-gray-900">{{ $machineType->brand ?? '-' }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <div class="text-gray-900">{{ $machineType->description ?? '-' }}</div>
                </div>
            </div>
            
            <!-- Photo Section -->
            @if($machineType->photo)
                @php
                    $photoUrl = asset('storage/' . $machineType->photo);
                    $photoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($machineType->photo);
                @endphp
                @if($photoExists)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-500 mb-3">Photo</label>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 inline-block">
                            <img src="{{ $photoUrl }}" 
                                 alt="Machine Type Photo" 
                                 class="max-w-md max-h-96 object-contain rounded-lg shadow-md cursor-pointer hover:opacity-90 transition-opacity"
                                 onclick="openPhotoModal('{{ $photoUrl }}')"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display:none;" class="text-sm text-red-500">
                                Photo tidak dapat dimuat
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Klik photo untuk memperbesar</p>
                    </div>
                @else
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-500 mb-3">Photo</label>
                        <div class="text-sm text-red-500">
                            Photo tidak ditemukan: {{ $machineType->photo }}
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Maintenance Points Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Maintenance Points</h2>
                <span class="text-sm text-gray-500">Total: {{ $machineType->maintenancePoints->count() }} points</span>
            </div>

            <div class="space-y-6">
                @foreach(['autonomous' => 'Autonomous Maintenance', 'preventive' => 'Preventive Maintenance', 'predictive' => 'Predictive Maintenance'] as $categoryKey => $categoryLabel)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg mb-2">
                            <h3 class="text-md font-semibold">{{ $categoryLabel }}</h3>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @forelse(($points[$categoryKey] ?? collect()) as $point)
                                <div class="bg-white rounded p-3 border border-gray-200">
                                    <div class="flex items-start gap-3">
                                        @if($point->photo)
                                            <div class="flex-shrink-0">
                                                <img src="{{ Storage::url($point->photo) }}" alt="Photo" class="w-12 h-20 object-cover rounded border cursor-pointer" onclick="openPhotoModal('{{ Storage::url($point->photo) }}')">
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-lg font-semibold text-gray-900">{{ $point->name }}</p>
                                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $point->instruction ?? '-' }}</p>
                                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                                        @if($point->frequency_type)
                                                            <span class="text-sm text-gray-400">Periode: {{ ucfirst($point->frequency_type) }} ({{ $point->frequency_value ?? 1 }}x)</span>
                                                        @endif
                                                        <span class="text-sm text-gray-400">Urutan: {{ $point->sequence }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-sm text-gray-500 py-4">
                                    Belum ada point {{ strtolower($categoryLabel) }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal with Zoom -->
<div id="photoModal" class="fixed inset-0 z-50 hidden overflow-hidden bg-black bg-opacity-90" onclick="closePhotoModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Zoom Controls -->
            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                <button onclick="zoomIn()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                <button onclick="zoomOut()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <button onclick="resetZoom()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2 text-xs px-3">
                    Reset
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="overflow-auto w-full h-full flex items-center justify-center" id="photoModalContainer">
                <img id="photoModalImg" src="" alt="Photo" class="max-w-none transition-transform duration-200 cursor-move" style="transform-origin: center;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentZoom = 1;
const minZoom = 0.5;
const maxZoom = 5;
const zoomStep = 0.25;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

function openPhotoModal(photoUrl) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('photoModalImg');
    const container = document.getElementById('photoModalContainer');
    
    img.src = photoUrl;
    currentZoom = 1;
    img.style.transform = `scale(${currentZoom})`;
    img.style.cursor = 'move';
    
    // Reset scroll position
    container.scrollLeft = 0;
    container.scrollTop = 0;
    
    modal.classList.remove('hidden');
    
    // Enable mouse wheel zoom
    container.addEventListener('wheel', handleWheelZoom, { passive: false });
    
    // Enable drag to pan
    img.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    const container = document.getElementById('photoModalContainer');
    
    modal.classList.add('hidden');
    
    // Remove event listeners
    container.removeEventListener('wheel', handleWheelZoom);
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
}

function handleWheelZoom(e) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    const newZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom + delta));
    setZoom(newZoom, e.clientX, e.clientY);
}

function zoomIn() {
    const newZoom = Math.min(maxZoom, currentZoom + zoomStep);
    setZoom(newZoom);
}

function zoomOut() {
    const newZoom = Math.max(minZoom, currentZoom - zoomStep);
    setZoom(newZoom);
}

function resetZoom() {
    setZoom(1);
    const container = document.getElementById('photoModalContainer');
    container.scrollLeft = 0;
    container.scrollTop = 0;
}

function setZoom(zoom, mouseX = null, mouseY = null) {
    const img = document.getElementById('photoModalImg');
    const container = document.getElementById('photoModalContainer');
    
    const oldZoom = currentZoom;
    currentZoom = zoom;
    img.style.transform = `scale(${currentZoom})`;
    
    if (mouseX && mouseY && oldZoom !== 1) {
        // Zoom towards mouse position
        const rect = container.getBoundingClientRect();
        const x = mouseX - rect.left;
        const y = mouseY - rect.top;
        
        const scrollX = x - (x - container.scrollLeft) * (zoom / oldZoom);
        const scrollY = y - (y - container.scrollTop) * (zoom / oldZoom);
        
        container.scrollLeft = scrollX;
        container.scrollTop = scrollY;
    } else {
        // Zoom from center
        const centerX = container.scrollLeft + container.clientWidth / 2;
        const centerY = container.scrollTop + container.clientHeight / 2;
        
        const scrollX = centerX - (centerX - container.scrollLeft) * (zoom / oldZoom);
        const scrollY = centerY - (centerY - container.scrollTop) * (zoom / oldZoom);
        
        container.scrollLeft = scrollX;
        container.scrollTop = scrollY;
    }
}

function startDrag(e) {
    if (currentZoom <= 1) return;
    isDragging = true;
    const container = document.getElementById('photoModalContainer');
    startX = e.pageX - container.offsetLeft;
    startY = e.pageY - container.offsetTop;
    scrollLeft = container.scrollLeft;
    scrollTop = container.scrollTop;
    e.preventDefault();
}

function drag(e) {
    if (!isDragging) return;
    e.preventDefault();
    const container = document.getElementById('photoModalContainer');
    const x = e.pageX - container.offsetLeft;
    const y = e.pageY - container.offsetTop;
    const walkX = (x - startX);
    const walkY = (y - startY);
    container.scrollLeft = scrollLeft - walkX;
    container.scrollTop = scrollTop - walkY;
}

function stopDrag() {
    isDragging = false;
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

