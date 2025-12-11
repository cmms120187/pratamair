@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Downtime ERP</h1>
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('downtime_erp.update', $row->id) }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')
            <!-- Informasi Mesin -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Informasi Mesin</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4">
                    <div class="md:col-span-2">
                        <label class="block mb-2 font-semibold text-gray-700">ID Mesin</label>
                        <input type="text" 
                               name="idMachine" 
                               id="idMachine" 
                               value="{{ old('idMachine', $row->idMachine) }}" 
                               placeholder="Masukkan ID Mesin atau scan barcode"
                               class="w-full border rounded px-3 py-2 @error('idMachine') border-red-500 @enderror">
                        @error('idMachine')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">&nbsp;</label>
                        <button type="button" 
                                id="scan_barcode_btn"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center justify-center gap-2"
                                title="Scan Barcode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>Scan</span>
                        </button>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">&nbsp;</label>
                        <button type="button" 
                                id="search_machine_btn"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">Search</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Mesin</label>
                        <input type="text" name="typeMachine" value="{{ old('typeMachine', $row->typeMachine) }}" class="w-full border rounded px-3 py-2 @error('typeMachine') border-red-500 @enderror">
                        @error('typeMachine')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Model Mesin</label>
                        <input type="text" name="modelMachine" value="{{ old('modelMachine', $row->modelMachine) }}" class="w-full border rounded px-3 py-2 @error('modelMachine') border-red-500 @enderror">
                        @error('modelMachine')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Brand Mesin</label>
                        <input type="text" name="brandMachine" value="{{ old('brandMachine', $row->brandMachine) }}" class="w-full border rounded px-3 py-2 @error('brandMachine') border-red-500 @enderror">
                        @error('brandMachine')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Room/Plant</label>
                        <input type="text" name="roomName" value="{{ old('roomName', $row->roomName) }}" class="w-full border rounded px-3 py-2 @error('roomName') border-red-500 @enderror">
                        @error('roomName')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Plant</label>
                        <input type="text" name="plant" value="{{ old('plant', $row->plant) }}" class="w-full border rounded px-3 py-2 @error('plant') border-red-500 @enderror">
                        @error('plant')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Process</label>
                        <input type="text" name="process" value="{{ old('process', $row->process) }}" class="w-full border rounded px-3 py-2 @error('process') border-red-500 @enderror">
                        @error('process')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Line</label>
                        <input type="text" name="line" value="{{ old('line', $row->line) }}" class="w-full border rounded px-3 py-2 @error('line') border-red-500 @enderror">
                        @error('line')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Downtime -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Downtime</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', $row->date) }}" required class="w-full border rounded px-3 py-2 @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Stop Production</label>
                        <input type="text" name="stopProduction" value="{{ old('stopProduction', $row->stopProduction) }}" class="w-full border rounded px-3 py-2 @error('stopProduction') border-red-500 @enderror" placeholder="HH:mm">
                        @error('stopProduction')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Respon Mechanic</label>
                        <input type="text" name="responMechanic" value="{{ old('responMechanic', $row->responMechanic) }}" class="w-full border rounded px-3 py-2 @error('responMechanic') border-red-500 @enderror" placeholder="HH:mm">
                        @error('responMechanic')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Start Production</label>
                        <input type="text" name="startProduction" value="{{ old('startProduction', $row->startProduction) }}" class="w-full border rounded px-3 py-2 @error('startProduction') border-red-500 @enderror" placeholder="HH:mm">
                        @error('startProduction')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Duration (minutes)</label>
                        <input type="text" name="duration" value="{{ old('duration', $row->duration) }}" class="w-full border rounded px-3 py-2 @error('duration') border-red-500 @enderror">
                        @error('duration')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Standard Time</label>
                        <input type="text" name="Standar_Time" value="{{ old('Standar_Time', $row->Standar_Time) }}" class="w-full border rounded px-3 py-2 @error('Standar_Time') border-red-500 @enderror">
                        @error('Standar_Time')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Problem</label>
                        <input type="text" name="problemDowntime" value="{{ old('problemDowntime', $row->problemDowntime) }}" class="w-full border rounded px-3 py-2 @error('problemDowntime') border-red-500 @enderror">
                        @error('problemDowntime')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Problem MM</label>
                        <input type="text" name="Problem_MM" value="{{ old('Problem_MM', $row->Problem_MM) }}" class="w-full border rounded px-3 py-2 @error('Problem_MM') border-red-500 @enderror">
                        @error('Problem_MM')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Reason</label>
                        <input type="text" name="reasonDowntime" value="{{ old('reasonDowntime', $row->reasonDowntime) }}" class="w-full border rounded px-3 py-2 @error('reasonDowntime') border-red-500 @enderror">
                        @error('reasonDowntime')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Action</label>
                        <input type="text" name="actionDowtime" value="{{ old('actionDowtime', $row->actionDowtime) }}" class="w-full border rounded px-3 py-2 @error('actionDowtime') border-red-500 @enderror">
                        @error('actionDowtime')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Group Problem</label>
                        <input type="text" name="groupProblem" value="{{ old('groupProblem', $row->groupProblem) }}" class="w-full border rounded px-3 py-2 @error('groupProblem') border-red-500 @enderror">
                        @error('groupProblem')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Detail -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Detail</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Mekanik</label>
                        <input type="text" name="idMekanik" value="{{ old('idMekanik', $row->idMekanik) }}" class="w-full border rounded px-3 py-2 @error('idMekanik') border-red-500 @enderror">
                        @error('idMekanik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Mekanik</label>
                        <input type="text" name="nameMekanik" value="{{ old('nameMekanik', $row->nameMekanik) }}" class="w-full border rounded px-3 py-2 @error('nameMekanik') border-red-500 @enderror">
                        @error('nameMekanik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Leader</label>
                        <input type="text" name="idLeader" value="{{ old('idLeader', $row->idLeader) }}" class="w-full border rounded px-3 py-2 @error('idLeader') border-red-500 @enderror">
                        @error('idLeader')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Leader</label>
                        <input type="text" name="nameLeader" value="{{ old('nameLeader', $row->nameLeader) }}" class="w-full border rounded px-3 py-2 @error('nameLeader') border-red-500 @enderror">
                        @error('nameLeader')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Coordinator</label>
                        <input type="text" name="idCoord" value="{{ old('idCoord', $row->idCoord) }}" class="w-full border rounded px-3 py-2 @error('idCoord') border-red-500 @enderror">
                        @error('idCoord')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Coordinator</label>
                        <input type="text" name="nameCoord" value="{{ old('nameCoord', $row->nameCoord) }}" class="w-full border rounded px-3 py-2 @error('nameCoord') border-red-500 @enderror">
                        @error('nameCoord')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Part</label>
                        <input type="text" name="Part" value="{{ old('Part', $row->Part) }}" class="w-full border rounded px-3 py-2 @error('Part') border-red-500 @enderror">
                        @error('Part')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Update</button>
                <a href="{{ route('downtime_erp.index') }}" class="text-gray-600 hover:text-gray-800 font-semibold">Batal</a>
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
                    <h3 class="text-lg font-semibold text-gray-900">Scan Barcode Machine</h3>
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
let codeReader = null;
if (typeof ZXing !== 'undefined') {
    codeReader = new ZXing.BrowserMultiFormatReader();
}

const scanBarcodeBtn = document.getElementById('scan_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeStatus = document.getElementById('barcode_status');
const startBarcodeBtn = document.getElementById('start_barcode_btn');

scanBarcodeBtn.addEventListener('click', function() {
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
                
                // Set idMachine value and auto search
                const idMachineInput = document.getElementById('idMachine');
                if (idMachineInput) {
                    idMachineInput.value = scannedCode;
                    // Auto search after scan
                    setTimeout(() => {
                        searchMachineById(scannedCode);
                    }, 300);
                }
                
                stopBarcodeScanner();
                barcodeModal.classList.add('hidden');
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

// Search machine function
async function searchMachineById(idMachine) {
    if (!idMachine || !idMachine.trim()) {
        return;
    }
    
    try {
        const response = await fetch('{{ route('downtime_erp.search-machine') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ idMachine: idMachine })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Auto-fill form fields
            const machine = data.machine;
            document.querySelector('input[name="typeMachine"]').value = machine.typeMachine || '';
            document.querySelector('input[name="modelMachine"]').value = machine.modelMachine || '';
            document.querySelector('input[name="brandMachine"]').value = machine.brandMachine || '';
            document.querySelector('input[name="roomName"]').value = machine.roomName || '';
            document.querySelector('input[name="plant"]').value = machine.plant || '';
            document.querySelector('input[name="process"]').value = machine.process || '';
            document.querySelector('input[name="line"]').value = machine.line || '';
        } else {
            console.warn('Machine not found:', data.message);
        }
    } catch (error) {
        console.error('Error searching machine:', error);
    }
}

// Add click handler to search button
document.getElementById('search_machine_btn').addEventListener('click', function() {
    const idMachine = document.getElementById('idMachine').value;
    if (idMachine) {
        searchMachineById(idMachine);
    }
});
</script>
@endsection
