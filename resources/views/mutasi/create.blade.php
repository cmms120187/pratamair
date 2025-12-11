@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Mutasi Mesin</h1>
            <p class="text-sm text-gray-600">Pindahkan mesin dari room lama ke room baru</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('mutasi.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="machine_search" class="block text-sm font-semibold text-gray-700 mb-2">Machine ERP <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" 
                           id="machine_search" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-20 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('machine_erp_id') border-red-500 @enderror" 
                           placeholder="Ketik ID Machine atau scan barcode/QR code..."
                           autocomplete="off">
                    <button type="button" 
                            id="scan_barcode_btn" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1"
                            title="Scan Barcode/QR Code">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Scan
                    </button>
                    <div id="machine_dropdown" class="hidden absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="top: 100%; left: 0; right: 0; width: 100%; max-width: 100%; max-height: 12rem; overflow-y: auto; box-sizing: border-box;"></div>
                </div>
                <input type="hidden" name="machine_erp_id" id="machine_erp_id" required>
                <div id="selected_machine" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Machine Terpilih:</div>
                            <div id="selected_machine_id" class="text-sm text-gray-700 font-semibold"></div>
                            <div id="selected_machine_info" class="text-xs text-gray-500"></div>
                        </div>
                        <button type="button" id="clear_machine" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                    </div>
                </div>
                @error('machine_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="old_room_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Room Lama (Auto-fill dari lokasi saat ini)</label>
                <select name="old_room_erp_id" 
                        id="old_room_erp_id" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('old_room_erp_id') border-red-500 @enderror">
                    <option value="">-- Pilih Room Lama (opsional) --</option>
                    @foreach($roomErps as $room)
                        <option value="{{ $room->id }}" {{ old('old_room_erp_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->kode_room ? $room->kode_room . ' - ' : '' }}{{ $room->name }}
                            @if($room->plant_name)
                                ({{ $room->plant_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Akan otomatis terisi dengan room saat ini dari Machine ERP yang dipilih</p>
                @error('old_room_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="new_room_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Room Baru <span class="text-red-500">*</span></label>
                <select name="new_room_erp_id" 
                        id="new_room_erp_id" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('new_room_erp_id') border-red-500 @enderror" 
                        required>
                    <option value="">-- Pilih Room Baru --</option>
                    @foreach($roomErps as $room)
                        <option value="{{ $room->id }}" {{ old('new_room_erp_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->kode_room ? $room->kode_room . ' - ' : '' }}{{ $room->name }}
                            @if($room->plant_name)
                                ({{ $room->plant_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('new_room_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mutasi <span class="text-red-500">*</span></label>
                <input type="date" 
                       name="date" 
                       id="date" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('date') border-red-500 @enderror" 
                       value="{{ old('date', date('Y-m-d')) }}" 
                       required>
                @error('date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">Alasan Mutasi</label>
                <input type="text" 
                       name="reason" 
                       id="reason" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('reason') border-red-500 @enderror" 
                       value="{{ old('reason') }}" 
                       placeholder="Masukkan alasan mutasi">
                @error('reason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('description') border-red-500 @enderror" 
                          placeholder="Masukkan deskripsi mutasi">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>Perhatian:</strong> Setelah mutasi dibuat, room_name, plant_name, process_name, dan line_name di Machine ERP akan otomatis diupdate sesuai dengan Room Baru yang dipilih.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Mutasi
                </button>
                <a href="{{ route('mutasi.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeBarcodeModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Scan Barcode/QR Code</h3>
                <button onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <video id="barcode_video" class="w-full rounded-lg" autoplay playsinline></video>
                <canvas id="barcode_canvas" class="hidden"></canvas>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Atau masukkan manual:</label>
                <input type="text" 
                       id="manual_barcode_input" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Masukkan ID Machine">
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="startBarcodeScan()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Mulai Scan</button>
                <button type="button" onclick="stopBarcodeScan()" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">Stop</button>
                <button type="button" onclick="closeBarcodeModal()" class="text-gray-600 hover:text-gray-800">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
// Machine data from server
const machines = @json($machinesData);

// Room ERP data from server
const rooms = @json($roomsData);

// DOM elements
const machineSearch = document.getElementById('machine_search');
const machineId = document.getElementById('machine_erp_id');
const machineDropdown = document.getElementById('machine_dropdown');
const selectedMachine = document.getElementById('selected_machine');
const selectedMachineId = document.getElementById('selected_machine_id');
const selectedMachineInfo = document.getElementById('selected_machine_info');
const clearMachine = document.getElementById('clear_machine');
const scanBarcodeBtn = document.getElementById('scan_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeCanvas = document.getElementById('barcode_canvas');
const manualBarcodeInput = document.getElementById('manual_barcode_input');

let stream = null;
let scanning = false;

// Auto-complete functionality
machineSearch.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    if (searchTerm.length === 0) {
        machineDropdown.classList.add('hidden');
        return;
    }
    
    const filtered = machines.filter(m => 
        (m.idMachine && m.idMachine.toLowerCase().includes(searchTerm)) ||
        (m.room_name && m.room_name.toLowerCase().includes(searchTerm)) ||
        (m.plant_name && m.plant_name.toLowerCase().includes(searchTerm)) ||
        (m.process_name && m.process_name.toLowerCase().includes(searchTerm)) ||
        (m.line_name && m.line_name.toLowerCase().includes(searchTerm))
    );
    
    if (filtered.length === 0) {
        machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
        machineDropdown.classList.remove('hidden');
        return;
    }
    
    machineDropdown.innerHTML = filtered.slice(0, 8).map(m => {
        const info = m.room_name ? `Room: ${m.room_name}` : 'Belum ada room';
        const idEscaped = JSON.stringify(String(m.id));
        const idMachineEscaped = JSON.stringify(m.idMachine || '');
        const infoEscaped = JSON.stringify(info);
        return `
            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors active:bg-blue-100" 
                 data-machine-id="${String(m.id)}"
                 data-machine-name="${(m.idMachine || '').replace(/"/g, '&quot;')}"
                 data-machine-room="${(m.room_name || '').replace(/"/g, '&quot;')}"
                 style="user-select: none; -webkit-user-select: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <div class="font-semibold text-gray-900 truncate">${m.idMachine || ''}</div>
                <div class="text-xs text-gray-500 truncate">${info}</div>
            </div>
        `;
    }).join('');
    
    // Add click event listeners to each dropdown item
    machineDropdown.querySelectorAll('[data-machine-id]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const machineId = this.getAttribute('data-machine-id');
            const machineName = this.getAttribute('data-machine-name');
            const machineRoom = this.getAttribute('data-machine-room');
            const info = machineRoom ? `Room: ${machineRoom}` : 'Belum ada room';
            selectMachine(machineId, machineName, info);
        });
    });
    
    machineDropdown.classList.remove('hidden');
});

// Select machine
window.selectMachine = function(id, idMachine, info) {
    // Convert id to string for comparison
    const machineIdStr = String(id);
    const idMachineStr = String(idMachine || '');
    
    // Set machine ID and search value
    machineId.value = machineIdStr;
    machineSearch.value = idMachineStr;
    selectedMachineId.textContent = idMachineStr;
    selectedMachineInfo.textContent = info || 'Belum ada room';
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.blur();
    
    // Auto-fill old room if machine has room_name
    const machine = machines.find(m => String(m.id) === machineIdStr);
    if (machine && machine.room_name) {
        // Find room by name (exact match first, then case-insensitive)
        const matchingRoom = rooms.find(r => {
            const roomName = r.name || '';
            const machineRoomName = machine.room_name || '';
            return roomName === machineRoomName || roomName.toLowerCase() === machineRoomName.toLowerCase();
        });
        
        if (matchingRoom) {
            const oldRoomSelect = document.getElementById('old_room_erp_id');
            if (oldRoomSelect) {
                oldRoomSelect.value = String(matchingRoom.id);
                // Trigger change event to ensure form validation
                oldRoomSelect.dispatchEvent(new Event('change'));
            }
        }
    }
};

// Clear machine
clearMachine.addEventListener('click', function() {
    machineId.value = '';
    machineSearch.value = '';
    selectedMachine.classList.add('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!machineSearch.contains(e.target) && !machineDropdown.contains(e.target)) {
        machineDropdown.classList.add('hidden');
    }
});

// Barcode scanner functions
scanBarcodeBtn.addEventListener('click', function() {
    barcodeModal.classList.remove('hidden');
});

function closeBarcodeModal() {
    barcodeModal.classList.add('hidden');
    stopBarcodeScan();
    manualBarcodeInput.value = '';
}

function startBarcodeScan() {
    scanning = true;
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
    .then(function(mediaStream) {
        stream = mediaStream;
        barcodeVideo.srcObject = mediaStream;
        barcodeVideo.play();
        scanBarcode();
    })
    .catch(function(err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
    });
}

function stopBarcodeScan() {
    scanning = false;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    barcodeVideo.srcObject = null;
}

function scanBarcode() {
    if (!scanning) return;
    
    const context = barcodeCanvas.getContext('2d');
    barcodeCanvas.width = barcodeVideo.videoWidth;
    barcodeCanvas.height = barcodeVideo.videoHeight;
    context.drawImage(barcodeVideo, 0, 0, barcodeCanvas.width, barcodeCanvas.height);
    
    // Try to read barcode using jsQR library (if available) or manual input
    // For now, we'll use manual input as fallback
    requestAnimationFrame(scanBarcode);
}

// Manual barcode input
manualBarcodeInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const barcodeValue = this.value.trim();
        if (barcodeValue) {
            // Find machine by ID Machine
            const machine = machines.find(m => (m.idMachine && (m.idMachine === barcodeValue || m.idMachine.toLowerCase() === barcodeValue.toLowerCase())));
            if (machine) {
                selectMachine(machine.id, machine.idMachine, machine.room_name ? `Room: ${machine.room_name}` : 'Belum ada room');
                closeBarcodeModal();
            } else {
                alert('Machine dengan ID "' + barcodeValue + '" tidak ditemukan.');
            }
        }
    }
});

// Also allow direct paste/input in search field
machineSearch.addEventListener('paste', function(e) {
    setTimeout(() => {
        const value = this.value.trim();
        const machine = machines.find(m => (m.idMachine && (m.idMachine === value || m.idMachine.toLowerCase() === value.toLowerCase())));
        if (machine) {
            selectMachine(machine.id, machine.idMachine, machine.room_name ? `Room: ${machine.room_name}` : 'Belum ada room');
        }
    }, 100);
});
</script>
@endsection

