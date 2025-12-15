@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{
    showFilter: false,
    searchMachine: '{{ request('search_machine') }}',
    dateFrom: '{{ request('date_from') }}',
    dateTo: '{{ request('date_to') }}',
    plantId: '{{ request('plant_id') }}',
    processId: '{{ request('process_id') }}',
    lineId: '{{ request('line_id') }}',
    roomId: '{{ request('room_id') }}',
    machineTypeId: '{{ request('machine_type_id') }}',
    showBarcodeScanner: false,
    barcodeResult: '',
    init() {
        // Initialize barcode scanner if needed
    },
    openBarcodeScanner() {
        this.showBarcodeScanner = true;
        // Trigger barcode scanner modal
        setTimeout(() => {
            const video = document.getElementById('barcode-video');
            if (video && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(stream => {
                        video.srcObject = stream;
                        video.play();
                        this.startBarcodeDetection(video);
                    })
                    .catch(err => {
                        console.error('Error accessing camera:', err);
                        alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
                    });
            }
        }, 100);
    },
    startBarcodeDetection(video) {
        // Use ZXing library for barcode detection
        if (typeof ZXing === 'undefined') {
            console.error('ZXing library not loaded');
            return;
        }
        
        const codeReader = new ZXing.BrowserMultiFormatReader();
        codeReader.decodeFromVideoDevice(null, video.id, (result, err) => {
            if (result) {
                this.barcodeResult = result.getText();
                this.searchMachine = this.barcodeResult;
                this.closeBarcodeScanner();
                // Auto submit form
                document.getElementById('filterForm').submit();
            }
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('Barcode detection error:', err);
            }
        });
    },
    closeBarcodeScanner() {
        this.showBarcodeScanner = false;
        const video = document.getElementById('barcode-video');
        if (video && video.srcObject) {
            const stream = video.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            video.srcObject = null;
        }
    }
}">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Downtimes</h1>
            <div class="flex items-center gap-3">
                <button @click="showFilter = !showFilter" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('downtimes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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

        <!-- Filter Section -->
        <div x-show="showFilter" x-cloak class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6" style="display: none;">
            <form id="filterForm" method="GET" action="{{ route('downtimes.index') }}" class="space-y-4">
                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               x-model="dateFrom"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               x-model="dateTo"
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <!-- Search Machine ID with Barcode -->
                <div>
                    <label for="search_machine" class="block text-sm font-medium text-gray-700 mb-2">Search Machine ID</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="search_machine" 
                               name="search_machine" 
                               x-model="searchMachine"
                               placeholder="Masukkan ID Mesin atau scan barcode"
                               class="flex-1 border rounded px-3 py-2">
                        <button type="button" 
                                @click="openBarcodeScanner()"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Scan
                        </button>
                    </div>
                </div>

                <!-- Plant, Process, Line, Room, Machine Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="plant_id" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                        <select id="plant_id" 
                                name="plant_id" 
                                x-model="plantId"
                                class="w-full border rounded px-3 py-2">
                            <option value="">All Plants</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="process_id" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                        <select id="process_id" 
                                name="process_id" 
                                x-model="processId"
                                class="w-full border rounded px-3 py-2">
                            <option value="">All Processes</option>
                            @foreach($processes as $process)
                                <option value="{{ $process->id }}" {{ request('process_id') == $process->id ? 'selected' : '' }}>{{ $process->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                        <select id="line_id" 
                                name="line_id" 
                                x-model="lineId"
                                class="w-full border rounded px-3 py-2">
                            <option value="">All Lines</option>
                            @foreach($lines as $line)
                                <option value="{{ $line->id }}" {{ request('line_id') == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                        <select id="room_id" 
                                name="room_id" 
                                x-model="roomId"
                                class="w-full border rounded px-3 py-2">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="machine_type_id" class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                        <select id="machine_type_id" 
                                name="machine_type_id" 
                                x-model="machineTypeId"
                                class="w-full border rounded px-3 py-2">
                            <option value="">All Machine Types</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ request('machine_type_id') == $machineType->id ? 'selected' : '' }}>{{ $machineType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Apply Filter
                    </button>
                    <a href="{{ route('downtimes.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plant</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Room</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID Machine</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Machine Type</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Duration (min)</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Problem</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Mekanik</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($downtimes as $downtime)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($downtimes->currentPage() - 1) * $downtimes->perPage() }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->date->format('d/m/Y') }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->machine->plant->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->machine->room->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold">{{ $downtime->machine->idMachine ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->machine->machineType->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->duration ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $downtime->problem->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $downtime->mekanik->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('downtimes.show', $downtime->id) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                @if(Auth::user()->role !== 'mekanik')
                                    <a href="{{ route('downtimes.edit', ['downtime' => $downtime->id, 'page' => request('page', 1)]) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <form action="{{ route('downtimes.destroy', $downtime->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">No downtimes found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($downtimes->hasPages())
                <div class="mt-4">
                    {{ $downtimes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Barcode Scanner Modal -->
    <div x-show="showBarcodeScanner" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeBarcodeScanner()"
         @keydown.escape.window="closeBarcodeScanner()"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.stop class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Scan Barcode</h3>
                    <button @click="closeBarcodeScanner()" 
                            type="button"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="mb-4">
                    <video id="barcode-video" class="w-full rounded border" autoplay playsinline></video>
                </div>
                <div class="flex justify-end">
                    <button @click="closeBarcodeScanner()" 
                            type="button"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
