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
                            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 flex items-center justify-between">
                                <span class="text-sm text-blue-900">
                                    <span class="font-semibold" id="selected_machine_id"></span>
                                    <span class="text-blue-600" id="selected_machine_info"></span>
                                </span>
                                <button type="button" id="clear_machine" class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="standard_id" class="block text-sm font-medium text-gray-700 mb-2">Standard <span class="text-red-500">*</span></label>
                        <select name="standard_id" id="standard_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}">
                                    {{ $standard->name }}
                                    @if($standard->reference_code)
                                        ({{ $standard->reference_code }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="maintenance_category" class="block text-sm font-medium text-gray-700 mb-2">Maintenance Category</label>
                        <select name="maintenance_category" id="maintenance_category" class="w-full border rounded px-3 py-2" required>
                            <option value="predictive">Predictive</option>
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                        <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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
const machines = @json(\App\Models\Machine::with(['room.plant', 'room.process', 'room.line', 'machineType'])->get()->map(function($machine) {
    return [
        'id' => $machine->id,
        'idMachine' => $machine->idMachine,
        'machineType' => $machine->machineType->name ?? '-',
        'plant' => $machine->room->plant->name ?? '-',
        'process' => $machine->room->process->name ?? '-',
        'line' => $machine->room->line->name ?? '-',
    ];
}));

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

    if (searchTerm.length === 0) {
        machineDropdown.classList.add('hidden');
        return;
    }

    const filtered = machines.filter(m =>
        m.idMachine.toLowerCase().includes(searchTerm) ||
        m.machineType.toLowerCase().includes(searchTerm) ||
        m.plant.toLowerCase().includes(searchTerm) ||
        m.process.toLowerCase().includes(searchTerm) ||
        m.line.toLowerCase().includes(searchTerm)
    );

    if (filtered.length === 0) {
        machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
        machineDropdown.classList.remove('hidden');
        return;
    }

    machineDropdown.innerHTML = filtered.slice(0, 10).map(m => {
        const info = `${m.machineType} - (${m.plant} / ${m.process} / ${m.line})`;
        return `
        <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
             onclick="selectMachine(${m.id}, ${JSON.stringify(m.idMachine)}, ${JSON.stringify(info)})">
            <div class="font-semibold text-gray-900">${m.idMachine}</div>
            <div class="text-xs text-gray-500">${info}</div>
        </div>
    `;
    }).join('');

    machineDropdown.classList.remove('hidden');
});

window.selectMachine = function(id, idMachine, info) {
    machineId.value = id;
    machineSearch.value = idMachine;
    selectedMachineId.textContent = idMachine;
    selectedMachineInfo.textContent = info;
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.blur();
};

clearMachine.addEventListener('click', function() {
    machineId.value = '';
    machineSearch.value = '';
    selectedMachine.classList.add('hidden');
});

document.addEventListener('click', function(e) {
    if (!machineSearch.contains(e.target) && !machineDropdown.contains(e.target)) {
        machineDropdown.classList.add('hidden');
    }
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

                if (foundMachine) {
                    const info = `${foundMachine.machineType} - (${foundMachine.plant} / ${foundMachine.process} / ${foundMachine.line})`;
                    selectMachine(foundMachine.id, foundMachine.idMachine, info);
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
