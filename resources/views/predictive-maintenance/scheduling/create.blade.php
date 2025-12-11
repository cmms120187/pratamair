@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Predictive Maintenance Schedule</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('predictive-maintenance.scheduling.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">Machine <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="text"
                                       id="machine_id_search"
                                       placeholder="Cari ID Machine atau scan barcode..."
                                       class="w-full border rounded px-3 py-2 pr-10"
                                       autocomplete="off">
                                <input type="hidden"
                                       name="machine_id"
                                       id="machine_id"
                                       value="{{ old('machine_id') }}"
                                       required>
                                <div id="machine_id_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <!-- Options will be populated here -->
                                </div>
                            </div>
                            <button type="button"
                                    id="scan_machine_barcode_btn"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2"
                                    title="Scan Barcode">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span class="hidden sm:inline">Scan</span>
                            </button>
                        </div>
                        <div id="selected_machine" class="mt-2 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-blue-900">
                                        ID Mesin: <span id="selected_machine_id"></span>
                                    </span>
                                    <button type="button" id="clear_machine" class="text-blue-600 hover:text-blue-800" title="Hapus pilihan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="text-sm text-blue-800 space-y-1 border-t border-blue-200 pt-2 mt-2" id="selected_machine_info">
                                    <!-- Machine details will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Pilih Team Leader</label>
                        <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Team Leader</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                    @if($user->nik)
                                        ({{ $user->nik }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('predictive-maintenance.scheduling.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Create Schedule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @keydown.escape.window="closeBarcodeModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Scan Barcode</h3>
                    <button type="button" onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="barcode_scanner_container" class="mb-4">
                    <video id="barcode_video" class="w-full rounded border-2 border-gray-300" autoplay playsinline></video>
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

<script>
const machines = @json($machines ?? []);
console.log('Machines data:', machines);

let codeReader = null;
if (typeof ZXing !== 'undefined') {
    codeReader = new ZXing.BrowserMultiFormatReader();
}

const machineSearch = document.getElementById('machine_id_search');
const machineId = document.getElementById('machine_id');
const machineDropdown = document.getElementById('machine_id_dropdown');
const selectedMachine = document.getElementById('selected_machine');
const selectedMachineId = document.getElementById('selected_machine_id');
const selectedMachineInfo = document.getElementById('selected_machine_info');
const clearMachine = document.getElementById('clear_machine');
const scanBtn = document.getElementById('scan_machine_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeStatus = document.getElementById('barcode_status');
const startBarcodeBtn = document.getElementById('start_barcode_btn');

machineSearch.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();

    // Don't show dropdown if machine is already selected
    if (machineId.value && machineId.value !== '') {
        return;
    }

    if (searchTerm.length === 0) {
        machineDropdown.classList.add('hidden');
        return;
    }

    const filtered = machines.filter(m => {
        if (!m) return false;
        return (m.idMachine && typeof m.idMachine === 'string' && m.idMachine.toLowerCase().includes(searchTerm)) ||
               (m.machineType && typeof m.machineType === 'string' && m.machineType.toLowerCase().includes(searchTerm)) ||
               (m.modelName && typeof m.modelName === 'string' && m.modelName.toLowerCase().includes(searchTerm)) ||
               (m.brandName && typeof m.brandName === 'string' && m.brandName.toLowerCase().includes(searchTerm)) ||
               (m.plant && typeof m.plant === 'string' && m.plant.toLowerCase().includes(searchTerm)) ||
               (m.process && typeof m.process === 'string' && m.process.toLowerCase().includes(searchTerm)) ||
               (m.line && typeof m.line === 'string' && m.line.toLowerCase().includes(searchTerm)) ||
               (m.room && typeof m.room === 'string' && m.room.toLowerCase().includes(searchTerm));
    });

    if (filtered.length === 0) {
        machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
        machineDropdown.classList.remove('hidden');
        return;
    }

    machineDropdown.innerHTML = filtered.slice(0, 10).map((m, index) => {
        if (!m) return '';
        const info = `${m.machineType || '-'} - ${m.brandName || ''} ${m.modelName || '-'} (${m.plant || '-'} / ${m.process || '-'} / ${m.line || '-'} / ${m.room || '-'})`;
        return `
        <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 machine-option"
             data-machine-id="${m.id || ''}"
             data-id-machine="${(m.idMachine || '').replace(/"/g, '&quot;')}"
             data-machine-type="${(m.machineType || '').replace(/"/g, '&quot;')}"
             data-model-name="${(m.modelName || '').replace(/"/g, '&quot;')}"
             data-brand-name="${(m.brandName || '').replace(/"/g, '&quot;')}"
             data-room="${(m.room || '').replace(/"/g, '&quot;')}"
             data-plant="${(m.plant || '').replace(/"/g, '&quot;')}">
            <div class="font-semibold text-gray-900">${m.idMachine || '-'}</div>
            <div class="text-xs text-gray-500">${info}</div>
        </div>
    `;
    }).join('');
    
    // Add click event listeners to machine options
    machineDropdown.querySelectorAll('.machine-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const machineIdValue = this.getAttribute('data-machine-id');
            const idMachine = this.getAttribute('data-id-machine') || '';
            const machineType = this.getAttribute('data-machine-type') || '';
            const modelName = this.getAttribute('data-model-name') || '';
            const brandName = this.getAttribute('data-brand-name') || '';
            const room = this.getAttribute('data-room') || '';
            const plant = this.getAttribute('data-plant') || '';
            selectMachine(machineIdValue, idMachine, machineType, modelName, brandName, room, plant);
        });
    });

    machineDropdown.classList.remove('hidden');
});

window.selectMachine = function(id, idMachine, machineType, modelName, brandName, roomName, plantName) {
    if (!id || id === 'null' || id === null || id === '') {
        alert('Machine tidak valid.');
        return false;
    }
    
    // Set machine ID
    machineId.value = id;
    machineSearch.value = idMachine || '';
    selectedMachineId.textContent = idMachine || '-';
    
    // Display detailed machine information - decode HTML entities
    const decodeHtml = (html) => {
        if (!html) return '-';
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };
    
    const displayType = decodeHtml(machineType) || '-';
    const displayBrand = decodeHtml(brandName) || '';
    const displayModel = decodeHtml(modelName) || '-';
    const displayRoom = decodeHtml(roomName) || '-';
    const displayPlant = decodeHtml(plantName) || '-';
    
    selectedMachineInfo.innerHTML = `
        <div class="mb-2"><span class="font-semibold">Tipe Mesin:</span> <span class="text-blue-900">${displayType}</span></div>
        <div class="mb-2"><span class="font-semibold">Model:</span> <span class="text-blue-900">${displayBrand} ${displayModel}</span></div>
        <div class="mb-2"><span class="font-semibold">Room:</span> <span class="text-blue-900">${displayRoom}</span></div>
        <div><span class="font-semibold">Plant:</span> <span class="text-blue-900">${displayPlant}</span></div>
    `;
    
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    
    // Clear search input to prevent dropdown from reopening
    setTimeout(() => {
        machineSearch.blur();
        machineSearch.value = idMachine || '';
    }, 50);
    
    return false;
};

clearMachine.addEventListener('click', function(e) {
    e.stopPropagation();
    machineId.value = '';
    machineSearch.value = '';
    selectedMachine.classList.add('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.focus();
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const isClickInside = machineSearch.contains(e.target) || 
                         machineDropdown.contains(e.target) ||
                         (e.target.closest && e.target.closest('#machine_id_dropdown'));
    
    if (!isClickInside) {
        machineDropdown.classList.add('hidden');
    }
});

// Prevent dropdown from reopening when clicking on selected machine info
selectedMachine.addEventListener('click', function(e) {
    e.stopPropagation();
});

scanBtn.addEventListener('click', function() {
    barcodeModal.classList.remove('hidden');
});

window.closeBarcodeModal = function() {
    stopBarcodeScanner();
    barcodeModal.classList.add('hidden');
};

window.startBarcodeScanner = async function() {
    if (!codeReader) {
        barcodeStatus.textContent = 'Barcode scanner tidak tersedia.';
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

                const foundMachine = machines.find(m => m.idMachine === scannedCode);

                if (foundMachine && foundMachine.id) {
                    selectMachine(
                        foundMachine.id, 
                        foundMachine.idMachine || '', 
                        foundMachine.machineType || '-',
                        foundMachine.modelName || '-',
                        foundMachine.brandName || '-',
                        foundMachine.room || '-',
                        foundMachine.plant || '-'
                    );
                    stopBarcodeScanner();
                    barcodeModal.classList.add('hidden');
                } else {
                    barcodeStatus.textContent = 'Mesin dengan ID "' + scannedCode + '" tidak ditemukan.';
                    setTimeout(() => {
                        barcodeStatus.textContent = 'Scan barcode...';
                    }, 2000);
                }
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

barcodeModal.addEventListener('click', function(e) {
    if (e.target === barcodeModal) {
        stopBarcodeScanner();
        barcodeModal.classList.add('hidden');
    }
});
</script>
@endsection
