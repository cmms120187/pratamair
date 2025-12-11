@extends('layouts.app')
@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Machines</h1>
            <div class="flex items-center gap-3">
                <!-- Search ID Machine -->
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" 
                               id="search_machine_id" 
                               placeholder="Search ID Machine..."
                               value="{{ request('idMachine') }}"
                               class="border rounded px-3 py-2 pr-10 text-sm w-64"
                               autocomplete="off">
                        <button type="button" 
                                id="scan_machine_barcode_btn"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-green-600"
                                title="Scan Barcode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </button>
                    </div>
                    <button type="button" 
                            onclick="searchMachine()"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                </div>
                <!-- Filter Button -->
                <button type="button" 
                        @click.stop="filterModalOpen = true" 
                        class="relative bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['plant_id', 'process_id', 'line_id', 'room_id', 'type_id', 'brand_id', 'model_id', 'idMachine']))
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <a href="{{ route('machines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed;">
                <!-- <colgroup>
                    <col style="width: 1%;">
                    <col style="width: 3%;">
                    <col style="width: 3%;">
                    <col style="width: 6%;">
                    <col style="width: 2%;">
                    <col style="width: 4%;">
                    <col style="width: 5%;">
                    <col style="width: 11%;">
                    <col style="width: 7%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 5%;">
                </colgroup> -->
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">No</th>
                        <!-- <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th> -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID Machine</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Process</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Line</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Room</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Brand</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Model</th>
                        <!-- <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Created At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Updated At</th> -->
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($machines as $machine)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($machines->currentPage() - 1) * $machines->perPage() }}</td>
                        <!-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $machine->id }}</td> -->
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->idMachine }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->plant->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->process->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->line->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->room->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->machineType->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->brand->name ?? '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">{{ $machine->model->name ?? '' }}</td>
                        <!-- <td class="px-4 py-3 text-sm text-gray-500 overflow-hidden text-ellipsis whitespace-nowrap" style="max-width: 0;">{{ $machine->created_at ? $machine->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 overflow-hidden text-ellipsis whitespace-nowrap" style="max-width: 0;">{{ $machine->updated_at ? $machine->updated_at->format('Y-m-d H:i') : '-' }}</td> -->
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('machines.show', $machine->id) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('machines.edit', $machine->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('machines.destroy', $machine->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this machine?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500">No machines found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($machines->hasPages())
                <div class="mt-4">
                    {{ $machines->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Filter Modal -->
    <div x-show="filterModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="filterModalOpen = false"
         @keydown.escape.window="filterModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="filterModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6">
                <!-- Close Button -->
                <button @click="filterModalOpen = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter Settings
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Set your filter criteria and click Save to apply</p>
                </div>
                
                <!-- Modal Content -->
                <form method="GET" action="{{ route('machines.index') }}" id="filterForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                        <!-- ID Machine -->
                        <div>
                            <label for="modal_idMachine" class="block text-sm font-medium text-gray-700 mb-2">ID Machine</label>
                            <input type="text" name="idMachine" id="modal_idMachine" value="{{ request('idMachine') }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Search ID Machine...">
                        </div>
                        
                        <!-- Plant -->
                        <div>
                            <label for="modal_plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                            <select name="plant_id" id="modal_plant" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Plants</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Process -->
                        <div>
                            <label for="modal_process" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                            <select name="process_id" id="modal_process" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Processes</option>
                                @foreach($processes as $process)
                                    <option value="{{ $process->id }}" {{ request('process_id') == $process->id ? 'selected' : '' }}>{{ $process->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Line -->
                        <div>
                            <label for="modal_line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                            <select name="line_id" id="modal_line" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Lines</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->id }}" {{ request('line_id') == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Room -->
                        <div>
                            <label for="modal_room" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                            <select name="room_id" id="modal_room" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Type Machine -->
                        <div>
                            <label for="modal_type" class="block text-sm font-medium text-gray-700 mb-2">Type Machine</label>
                            <select name="type_id" id="modal_type" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Types</option>
                                @foreach($machineTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Brand -->
                        <div>
                            <label for="modal_brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                            <select name="brand_id" id="modal_brand" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Model -->
                        <div>
                            <label for="modal_model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                            <select name="model_id" id="modal_model" class="w-full border rounded px-3 py-2 text-sm">
                                <option value="">All Models</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                        <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                            Cancel
                        </button>
                        <a href="{{ route('machines.index') }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
                            Reset Filter
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded text-sm transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Barcode Scanner Modal -->
    <div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @keydown.escape.window="closeBarcodeModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Scan Barcode Machine</h3>
                        <button type="button" onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div id="barcode_scanner_container" class="mb-4">
                        <video id="barcode_video" class="w-full rounded border-2 border-gray-300" autoplay playsinline></video>
                        <canvas id="barcode_canvas" class="hidden"></canvas>
                    </div>
                    <div id="barcode_status" class="text-sm text-gray-600 mb-4 text-center"></div>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeBarcodeModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" id="start_barcode_btn" onclick="startBarcodeScanner();" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Start Camera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include ZXing library for barcode scanning -->
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest"></script>

<script>
let codeReader = null;

// Initialize ZXing
if (typeof ZXing !== 'undefined') {
    codeReader = new ZXing.BrowserMultiFormatReader();
}

// Search machine function
function searchMachine() {
    const searchValue = document.getElementById('search_machine_id').value.trim();
    if (searchValue) {
        window.location.href = "{{ route('machines.index') }}?idMachine=" + encodeURIComponent(searchValue);
    } else {
        window.location.href = "{{ route('machines.index') }}";
    }
}

// Enter key to search
document.getElementById('search_machine_id').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchMachine();
    }
});

// Barcode Scanner
const scanMachineBarcodeBtn = document.getElementById('scan_machine_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeStatus = document.getElementById('barcode_status');
const startBarcodeBtn = document.getElementById('start_barcode_btn');

scanMachineBarcodeBtn.addEventListener('click', function() {
    barcodeModal.classList.remove('hidden');
});

window.closeBarcodeModal = function() {
    stopBarcodeScanner();
    barcodeModal.classList.add('hidden');
};

window.startBarcodeScanner = async function() {
    if (!codeReader) {
        barcodeStatus.textContent = 'Barcode scanner tidak tersedia. Pastikan koneksi internet aktif.';
        return;
    }
    
    try {
        const videoInputDevices = await codeReader.listVideoInputDevices();
        
        if (videoInputDevices.length === 0) {
            barcodeStatus.textContent = 'Tidak ada kamera ditemukan.';
            return;
        }
        
        barcodeStatus.textContent = 'Mengaktifkan kamera...';
        startBarcodeBtn.disabled = true;
        
        const selectedDeviceId = videoInputDevices[0].deviceId;
        
        codeReader.decodeFromVideoDevice(selectedDeviceId, 'barcode_video', (result, err) => {
            if (result) {
                const scannedCode = result.getText();
                barcodeStatus.textContent = 'Barcode terdeteksi: ' + scannedCode;
                
                // Set search value and search
                document.getElementById('search_machine_id').value = scannedCode;
                stopBarcodeScanner();
                barcodeModal.classList.add('hidden');
                searchMachine();
            }
            
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('Barcode scan error:', err);
            }
        });
        
        barcodeStatus.textContent = 'Arahkan kamera ke barcode...';
    } catch (error) {
        console.error('Error starting barcode scanner:', error);
        barcodeStatus.textContent = 'Error: ' + error.message;
        startBarcodeBtn.disabled = false;
    }
};

window.stopBarcodeScanner = function() {
    if (codeReader) {
        codeReader.reset();
    }
    if (barcodeVideo.srcObject) {
        barcodeVideo.srcObject.getTracks().forEach(track => track.stop());
        barcodeVideo.srcObject = null;
    }
    barcodeStatus.textContent = '';
    startBarcodeBtn.disabled = false;
};

// Close modal when clicking outside
barcodeModal.addEventListener('click', function(e) {
    if (e.target === barcodeModal) {
        stopBarcodeScanner();
        barcodeModal.classList.add('hidden');
    }
});
</script>
@endsection
