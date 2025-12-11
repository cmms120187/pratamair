@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create Work Order</h1>
            <a href="{{ route('work-orders.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('work-orders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Date -->
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date <span class="text-red-500">*</span></label>
                        <input type="date"
                               name="order_date"
                               id="order_date"
                               value="{{ old('order_date', date('Y-m-d')) }}"
                               class="w-full border rounded px-3 py-2 @error('order_date') border-red-500 @enderror"
                               required>
                        @error('order_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                        <select name="priority"
                                id="priority"
                                class="w-full border rounded px-3 py-2 @error('priority') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Priority</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status"
                                id="status"
                                class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror"
                                required>
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="waiting_parts" {{ old('status') == 'waiting_parts' ? 'selected' : '' }}>Menunggu Sparepart</option>
                            <option value="order_parts" {{ old('status') == 'order_parts' ? 'selected' : '' }}>Order Sparepart</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Diperbaiki</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Machine -->
                    <div class="md:col-span-2">
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-1">Machine <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="text"
                                       id="machine_search"
                                       placeholder="Cari ID Machine atau scan barcode..."
                                       class="w-full border rounded px-3 py-2 pr-10 @error('machine_id') border-red-500 @enderror"
                                       autocomplete="off">
                                <input type="hidden"
                                       name="machine_id"
                                       id="machine_id"
                                       value="{{ old('machine_id') }}"
                                       required>
                                <div id="machine_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <!-- Options will be populated here -->
                                </div>
                            </div>
                            <button type="button"
                                    id="scan_barcode_btn"
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
                        @error('machine_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Problem Description -->
                    <div class="md:col-span-2">
                        <label for="problem_description" class="block text-sm font-medium text-gray-700 mb-1">Problem Description</label>
                        <textarea name="problem_description"
                                  id="problem_description"
                                  rows="3"
                                  class="w-full border rounded px-3 py-2 @error('problem_description') border-red-500 @enderror">{{ old('problem_description') }}</textarea>
                        @error('problem_description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select name="assigned_to"
                                id="assigned_to"
                                class="w-full border rounded px-3 py-2 @error('assigned_to') border-red-500 @enderror">
                            <option value="">Tidak ada</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->nik ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date"
                               name="due_date"
                               id="due_date"
                               value="{{ old('due_date') }}"
                               class="w-full border rounded px-3 py-2 @error('due_date') border-red-500 @enderror">
                        @error('due_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Repair Started At -->
                    <div>
                        <label for="repair_started_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal & Waktu Mulai Perbaikan</label>
                        <div class="flex gap-2">
                            <input type="date"
                                   name="repair_started_at"
                                   id="repair_started_at"
                                   value="{{ old('repair_started_at') }}"
                                   class="flex-1 border rounded px-3 py-2 @error('repair_started_at') border-red-500 @enderror">
                            <input type="time"
                                   name="repair_started_time"
                                   id="repair_started_time"
                                   value="{{ old('repair_started_time') }}"
                                   class="w-32 border rounded px-3 py-2 @error('repair_started_at') border-red-500 @enderror">
                        </div>
                        @error('repair_started_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Repair Completed At -->
                    <div>
                        <label for="repair_completed_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal & Waktu Selesai Perbaikan</label>
                        <div class="flex gap-2">
                            <input type="date"
                                   name="repair_completed_at"
                                   id="repair_completed_at"
                                   value="{{ old('repair_completed_at') }}"
                                   class="flex-1 border rounded px-3 py-2 @error('repair_completed_at') border-red-500 @enderror">
                            <input type="time"
                                   name="repair_completed_time"
                                   id="repair_completed_time"
                                   value="{{ old('repair_completed_time') }}"
                                   class="w-32 border rounded px-3 py-2 @error('repair_completed_at') border-red-500 @enderror">
                        </div>
                        @error('repair_completed_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimated Cost -->
                    <div>
                        <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost (Rp)</label>
                        <input type="number"
                               name="estimated_cost"
                               id="estimated_cost"
                               step="0.01"
                               min="0"
                               value="{{ old('estimated_cost') }}"
                               class="w-full border rounded px-3 py-2 @error('estimated_cost') border-red-500 @enderror">
                        @error('estimated_cost')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimated Duration -->
                    <div>
                        <label for="estimated_duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Estimated Duration (minutes)</label>
                        <input type="number"
                               name="estimated_duration_minutes"
                               id="estimated_duration_minutes"
                               min="0"
                               value="{{ old('estimated_duration_minutes') }}"
                               class="w-full border rounded px-3 py-2 @error('estimated_duration_minutes') border-red-500 @enderror">
                        @error('estimated_duration_minutes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Photo Before -->
                    <div class="md:col-span-2">
                        <label for="photo_before" class="block text-sm font-medium text-gray-700 mb-1">Photo Before</label>
                        <input type="file"
                               name="photo_before"
                               id="photo_before"
                               accept="image/*"
                               capture="environment"
                               class="w-full border rounded px-3 py-2 @error('photo_before') border-red-500 @enderror"
                               onchange="previewPhoto(this, 'photo_before_preview')">
                        <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 2MB). Klik untuk pilih file atau gunakan kamera langsung.</p>
                        <div id="photo_before_preview" class="mt-2 hidden">
                            <img id="photo_before_preview_img" src="" alt="Preview" class="w-32 h-32 object-cover rounded border-2 border-gray-300">
                        </div>
                        @error('photo_before')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sparepart yang Digunakan -->
                    <div class="md:col-span-2">
                        <label class="block mb-2 font-semibold text-gray-700">Sparepart yang Digunakan</label>
                        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                            <div id="parts-container" class="space-y-3">
                                <div class="part-row flex gap-3 items-end bg-white p-3 rounded border border-gray-200">
                                    <div class="flex-1">
                                        <label class="block mb-1 text-xs font-medium text-gray-600">Sparepart</label>
                                        <select name="parts[]" class="w-full border rounded px-3 py-2 part-select @error('parts.*') border-red-500 @enderror">
                                            <option value="">Pilih Sparepart</option>
                                            @foreach($parts as $part)
                                                @php
                                                    $oldParts = old('parts', []);
                                                    $isSelected = isset($oldParts[0]) && $oldParts[0] == $part->id;
                                                @endphp
                                                <option value="{{ $part->id }}" data-name="{{ $part->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $part->name }} ({{ $part->part_number ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-24">
                                        <label class="block mb-1 text-xs font-medium text-gray-600">Jumlah</label>
                                        @php
                                            $oldQuantities = old('part_quantities', []);
                                            $defaultQuantity = isset($oldQuantities[0]) ? $oldQuantities[0] : 1;
                                        @endphp
                                        <input type="number" name="part_quantities[]" value="{{ $defaultQuantity }}" min="1" class="w-full border rounded px-3 py-2 quantity-input @error('part_quantities.*') border-red-500 @enderror">
                                    </div>
                                    <button type="button" onclick="removePartRow(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded hidden remove-part-btn h-fit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="addPartRow()" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Sparepart
                            </button>
                            @error('parts.*')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('part_quantities.*')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('work-orders.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Create Work Order
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
                    <h3 class="text-lg font-semibold text-gray-900">Scan Barcode</h3>
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

<!-- Include ZXing library for barcode scanning -->
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest"></script>

<script>
// Machine data from server
const machines = @json($machinesArray);

let barcodeStream = null;
let codeReader = null;

// Machine search functionality
const machineSearch = document.getElementById('machine_search');
const machineId = document.getElementById('machine_id');
const machineDropdown = document.getElementById('machine_dropdown');
const selectedMachine = document.getElementById('selected_machine');
const selectedMachineId = document.getElementById('selected_machine_id');
const selectedMachineInfo = document.getElementById('selected_machine_info');
const clearMachine = document.getElementById('clear_machine');

machineSearch.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();

    if (searchTerm.length === 0) {
        machineDropdown.classList.add('hidden');
        return;
    }

    const filtered = machines.filter(m =>
        (m.idMachine && m.idMachine.toLowerCase().includes(searchTerm)) ||
        (m.type_name && m.type_name.toLowerCase().includes(searchTerm)) ||
        (m.plant_name && m.plant_name.toLowerCase().includes(searchTerm)) ||
        (m.process_name && m.process_name.toLowerCase().includes(searchTerm)) ||
        (m.line_name && m.line_name.toLowerCase().includes(searchTerm))
    );

    if (filtered.length === 0) {
        machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
        machineDropdown.classList.remove('hidden');
        return;
    }

    machineDropdown.innerHTML = filtered.slice(0, 10).map(m => {
        const info = `${m.type_name || '-'} - (${m.plant_name || '-'} / ${m.process_name || '-'} / ${m.line_name || '-'})`;
        // Escape untuk mencegah parsing error
        const idMachineEscaped = String(m.idMachine || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
        const infoEscaped = String(info).replace(/'/g, "\\'").replace(/"/g, '\\"');
        const machineId = m.machine_id || '';
        return `
        <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
             onclick="selectMachine('${machineId}', '${idMachineEscaped}', '${infoEscaped}')">
            <div class="font-semibold text-gray-900">${m.idMachine || '-'}</div>
            <div class="text-xs text-gray-500">${info}</div>
        </div>
    `;
    }).join('');

    machineDropdown.classList.remove('hidden');
});

// Select machine
window.selectMachine = function(id, idMachine, info) {
    // id bisa string atau number, pastikan diisi
    if (id && id !== '') {
        machineId.value = id;
    } else {
        // Jika tidak ada machine_id, cari dari Machine table berdasarkan idMachine
        // Atau biarkan kosong jika memang tidak ada di Machine table
        machineId.value = '';
    }
    machineSearch.value = idMachine;
    selectedMachineId.textContent = idMachine;
    selectedMachineInfo.textContent = info;
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.blur();
};

// Clear machine selection
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

// Barcode Scanner
const scanBarcodeBtn = document.getElementById('scan_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeCanvas = document.getElementById('barcode_canvas');
const barcodeStatus = document.getElementById('barcode_status');
const startBarcodeBtn = document.getElementById('start_barcode_btn');

// Initialize ZXing
if (typeof ZXing !== 'undefined') {
    codeReader = new ZXing.BrowserMultiFormatReader();
}

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

                // Find machine by ID Machine
                const foundMachine = machines.find(m => m.idMachine && m.idMachine.toLowerCase() === scannedCode.toLowerCase());

                if (foundMachine) {
                    const machineInfo = (foundMachine.type_name || '-') + ' - (' + 
                                       (foundMachine.plant_name || '-') + ' / ' + 
                                       (foundMachine.process_name || '-') + ' / ' + 
                                       (foundMachine.line_name || '-') + ')';
                    const machineId = foundMachine.machine_id || '';
                    selectMachine(machineId, foundMachine.idMachine, machineInfo);
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

// Parts management functions
const partsContainer = document.getElementById('parts-container');
const partsSelectOptions = @json($partsArray);

function addPartRow() {
    const partRow = document.createElement('div');
    partRow.className = 'part-row flex gap-3 items-end bg-white p-3 rounded border border-gray-200';
    // Gunakan String.fromCharCode untuk menghindari konflik dengan Blade parser
    const bracketOpen = String.fromCharCode(91); // [
    const bracketClose = String.fromCharCode(93); // ]
    const partsName = 'parts' + bracketOpen + bracketClose;
    const quantitiesName = 'part_quantities' + bracketOpen + bracketClose;
    partRow.innerHTML = '<div class="flex-1">' +
        '<label class="block mb-1 text-xs font-medium text-gray-600">Sparepart</label>' +
        '<select name="' + partsName + '" class="w-full border rounded px-3 py-2 part-select">' +
        '<option value="">Pilih Sparepart</option>' +
        partsSelectOptions.map(part => {
            return '<option value="' + part.id + '" data-name="' + part.name.replace(/"/g, '&quot;') + '">' +
                   part.name.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                   ' (' + (part.part_number || '-') + ')</option>';
        }).join('') +
        '</select>' +
        '</div>' +
        '<div class="w-24">' +
        '<label class="block mb-1 text-xs font-medium text-gray-600">Jumlah</label>' +
        '<input type="number" name="' + quantitiesName + '" value="1" min="1" class="w-full border rounded px-3 py-2 quantity-input">' +
        '</div>' +
        '<button type="button" onclick="removePartRow(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded remove-part-btn h-fit">' +
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' +
        '</svg>' +
        '</button>';
    partsContainer.appendChild(partRow);
    updateRemoveButtons();
}

function removePartRow(button) {
    button.closest('.part-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = partsContainer.querySelectorAll('.part-row');
    rows.forEach((row) => {
        const removeBtn = row.querySelector('.remove-part-btn');
        if (rows.length > 1) {
            removeBtn.classList.remove('hidden');
        } else {
            removeBtn.classList.add('hidden');
        }
    });
}

// Initialize remove buttons visibility
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});

// Close modal when clicking outside
barcodeModal.addEventListener('click', function(e) {
    if (e.target === barcodeModal) {
        stopBarcodeScanner();
        barcodeModal.classList.add('hidden');
    }
});

// Photo preview
function previewPhoto(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewId + '_img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }

        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}

// Set initial selected machine if exists
@if(old('machine_id'))
    @php
        $selectedMachine = $machines->firstWhere('id', old('machine_id'));
    @endphp
    @if($selectedMachine)
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $machineType = $selectedMachine->machineType->name ?? '-';
                $plant = ($selectedMachine->room && $selectedMachine->room->plant) ? $selectedMachine->room->plant->name : '-';
                $process = ($selectedMachine->room && $selectedMachine->room->process) ? $selectedMachine->room->process->name : '-';
                $line = ($selectedMachine->room && $selectedMachine->room->line) ? $selectedMachine->room->line->name : '-';
                $machineInfo = $machineType . ' - (' . $plant . ' / ' . $process . ' / ' . $line . ')';
            @endphp
            selectMachine({{ $selectedMachine->id }}, '{{ addslashes($selectedMachine->idMachine) }}', '{{ addslashes($machineInfo) }}');
        });
    @endif
@endif
</script>
@endsection
