@extends('layouts.app')
@section('content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maintenancePointSelect = document.getElementById('maintenance_point_id');
    const frequencyTypeSelect = document.getElementById('frequency_type');
    const frequencyValueInput = document.getElementById('frequency_value');
    
    if (maintenancePointSelect) {
        // Set initial values if maintenance point is already selected
        const selectedOption = maintenancePointSelect.options[maintenancePointSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const frequencyType = selectedOption.getAttribute('data-frequency-type');
            const frequencyValue = selectedOption.getAttribute('data-frequency-value');
            
            if (frequencyType) {
                frequencyTypeSelect.value = frequencyType;
            }
            if (frequencyValue) {
                frequencyValueInput.value = frequencyValue;
            }
        }
        
        maintenancePointSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const frequencyType = selectedOption.getAttribute('data-frequency-type');
                const frequencyValue = selectedOption.getAttribute('data-frequency-value');
                
                if (frequencyType) {
                    frequencyTypeSelect.value = frequencyType;
                }
                if (frequencyValue) {
                    frequencyValueInput.value = frequencyValue;
                }
            } else {
                frequencyTypeSelect.value = '';
                frequencyValueInput.value = 1;
            }
        });
    }
});
</script>
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Preventive Maintenance Schedule</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('preventive-maintenance.scheduling.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">Machine <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="text" 
                                       id="machine_id_search" 
                                       placeholder="Cari ID Machine atau scan barcode..."
                                       class="w-full border rounded px-3 py-2 pr-10 @error('machine_id') border-red-500 @enderror"
                                       autocomplete="off">
                                <input type="hidden" 
                                       name="machine_id" 
                                       id="machine_id" 
                                       value="{{ old('machine_id', $schedule->machine_id) }}"
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
                        <div id="selected_machine" class="mt-2 @if(!old('machine_id', $schedule->machine_id)) hidden @endif">
                            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 flex items-center justify-between">
                                <span class="text-sm text-blue-900">
                                    <span class="font-semibold" id="selected_machine_id">
                                        @if(old('machine_id', $schedule->machine_id))
                                            {{ $schedule->machine->idMachine ?? '-' }}
                                        @endif
                                    </span>
                                    <span class="text-blue-600" id="selected_machine_info">
                                        @if(old('machine_id', $schedule->machine_id))
                                            {{ $schedule->machine->machineType->name ?? '-' }} - ({{ $schedule->machine->plant->name ?? '-' }} / {{ $schedule->machine->process->name ?? '-' }} / {{ $schedule->machine->line->name ?? '-' }})
                                        @endif
                                    </span>
                                </span>
                                <button type="button" id="clear_machine" class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('machine_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="maintenance_point_id" class="block text-sm font-medium text-gray-700 mb-2">Tipe Maintenance <span class="text-red-500">*</span></label>
                        <select name="maintenance_point_id" id="maintenance_point_id" class="w-full border rounded px-3 py-2 @error('maintenance_point_id') border-red-500 @enderror" required>
                            <option value="">Pilih Tipe Maintenance (AM/PM/PdM)</option>
                            @foreach($maintenancePoints as $point)
                                <option value="{{ $point->id }}" 
                                        data-category="{{ $point->category }}"
                                        data-frequency-type="{{ $point->frequency_type ?? '' }}"
                                        data-frequency-value="{{ $point->frequency_value ?? 1 }}"
                                        {{ old('maintenance_point_id', $schedule->maintenance_point_id) == $point->id ? 'selected' : '' }}>
                                    @if($point->category == 'autonomous')
                                        AM - {{ $point->machineType->name ?? '-' }} - {{ $point->name }}
                                    @elseif($point->category == 'preventive')
                                        PM - {{ $point->machineType->name ?? '-' }} - {{ $point->name }}
                                    @elseif($point->category == 'predictive')
                                        PdM - {{ $point->machineType->name ?? '-' }} - {{ $point->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('maintenance_point_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $schedule->title) }}" class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror" required>
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description', $schedule->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="frequency_type" class="block text-sm font-medium text-gray-700 mb-2">Frequency Type <span class="text-red-500">*</span></label>
                        <select name="frequency_type" id="frequency_type" class="w-full border rounded px-3 py-2 @error('frequency_type') border-red-500 @enderror bg-gray-100" required readonly>
                            <option value="daily" {{ old('frequency_type', $schedule->frequency_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('frequency_type', $schedule->frequency_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('frequency_type', $schedule->frequency_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency_type', $schedule->frequency_type) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency_type', $schedule->frequency_type) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency_type', $schedule->frequency_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Otomatis dari Maintenance Point</p>
                        @error('frequency_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="frequency_value" class="block text-sm font-medium text-gray-700 mb-2">Frequency Value <span class="text-red-500">*</span></label>
                        <input type="number" name="frequency_value" id="frequency_value" value="{{ old('frequency_value', $schedule->frequency_value) }}" min="1" class="w-full border rounded px-3 py-2 @error('frequency_value') border-red-500 @enderror bg-gray-100" required readonly>
                        <p class="text-xs text-gray-500 mt-1">Otomatis dari Maintenance Point</p>
                        @error('frequency_value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $schedule->start_date->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2 @error('start_date') border-red-500 @enderror" required>
                        @error('start_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="preferred_time" class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
                        <input type="time" name="preferred_time" id="preferred_time" value="{{ old('preferred_time', $schedule->preferred_time ? \Carbon\Carbon::parse($schedule->preferred_time)->format('H:i') : '') }}" class="w-full border rounded px-3 py-2 @error('preferred_time') border-red-500 @enderror">
                        @error('preferred_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="estimated_duration" class="block text-sm font-medium text-gray-700 mb-2">Estimated Duration (minutes)</label>
                        <input type="number" name="estimated_duration" id="estimated_duration" value="{{ old('estimated_duration', $schedule->estimated_duration) }}" min="1" class="w-full border rounded px-3 py-2 @error('estimated_duration') border-red-500 @enderror">
                        @error('estimated_duration')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror" required>
                            <option value="active" {{ old('status', $schedule->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $schedule->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="completed" {{ old('status', $schedule->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $schedule->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                        <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2 @error('assigned_to') border-red-500 @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $schedule->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes', $schedule->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('preventive-maintenance.scheduling.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">
                        Update Schedule
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
const machines = @json($machines->map(function($machine) {
    return [
        'id' => $machine->id,
        'idMachine' => $machine->idMachine,
        'machineType' => $machine->machineType->name ?? '-',
        'plant' => $machine->plant->name ?? '-',
        'process' => $machine->process->name ?? '-',
        'line' => $machine->line->name ?? '-',
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

// Initialize selected machine on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentMachineId = document.getElementById('machine_id').value;
    if (currentMachineId) {
        const currentMachine = machines.find(m => m.id == currentMachineId);
        if (currentMachine) {
            machineSearch.value = currentMachine.idMachine;
        }
    }
});
</script>
@endsection

