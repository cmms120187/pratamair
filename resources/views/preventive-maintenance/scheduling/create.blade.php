@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Preventive Maintenance Schedule</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('preventive-maintenance.scheduling.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Type Machine -->
                    <div class="md:col-span-2">
                        <label for="type_machine_id" class="block text-sm font-medium text-gray-700 mb-2">Type Machine <span class="text-red-500">*</span></label>
                        <select name="type_machine_id" id="type_machine_id" class="w-full border rounded px-3 py-2 @error('type_machine_id') border-red-500 @enderror" required>
                            <option value="">Pilih Type Machine</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ old('type_machine_id') == $machineType->id ? 'selected' : '' }}>
                                    {{ $machineType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_machine_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Machine -->
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
                        @error('machine_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tipe Maintenance (Radio Buttons) -->
                    <div class="md:col-span-2" style="position: relative; z-index: 10; pointer-events: auto;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Maintenance <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-3" id="maintenance_category_container">
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" style="pointer-events: auto;">
                                <input type="radio" name="maintenance_category" value="autonomous" id="maintenance_category_autonomous" class="mr-2" {{ old('maintenance_category') == 'autonomous' ? 'checked' : '' }} required>
                                <span class="font-medium">AM - Autonomous Maintenance</span>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" style="pointer-events: auto;">
                                <input type="radio" name="maintenance_category" value="preventive" id="maintenance_category_preventive" class="mr-2" {{ old('maintenance_category') == 'preventive' ? 'checked' : '' }} required>
                                <span class="font-medium">PM - Preventive Maintenance</span>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" style="pointer-events: auto;">
                                <input type="radio" name="maintenance_category" value="predictive" id="maintenance_category_predictive" class="mr-2" {{ old('maintenance_category') == 'predictive' ? 'checked' : '' }} required>
                                <span class="font-medium">PdM - Predictive Maintenance</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="maintenance_category_help">Pilih Machine terlebih dahulu untuk mengaktifkan pilihan ini</p>
                        @error('maintenance_category')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Maintenance Points List (Informasi Point-point yang akan dimaintenance) -->
                    <div class="md:col-span-2" id="maintenance_points_container" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Points yang akan dieksekusi:</label>
                        <div id="maintenance_points_list" class="space-y-2 max-h-64 overflow-y-auto border rounded p-3 bg-gray-50">
                            <!-- Maintenance points will be loaded here -->
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Semua point di atas akan dieksekusi pada tanggal yang sama. Periode maintenance mengikuti setting di masing-masing point.</p>
                    </div>
                    
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full border rounded px-3 py-2 @error('start_date') border-red-500 @enderror" required>
                        <p class="text-xs text-gray-500 mt-1">End Date akan otomatis sampai akhir tahun</p>
                        @error('start_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full border rounded px-3 py-2 bg-gray-100 @error('end_date') border-red-500 @enderror" readonly>
                        <p class="text-xs text-gray-500 mt-1">Otomatis sampai akhir tahun</p>
                        @error('end_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Preferred Time -->
                    <div>
                        <label for="preferred_time" class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
                        <input type="time" name="preferred_time" id="preferred_time" value="{{ old('preferred_time') }}" class="w-full border rounded px-3 py-2 @error('preferred_time') border-red-500 @enderror">
                        @error('preferred_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Estimated Duration -->
                    <div>
                        <label for="estimated_duration" class="block text-sm font-medium text-gray-700 mb-2">Estimated Duration (minutes)</label>
                        <input type="number" name="estimated_duration" id="estimated_duration" value="{{ old('estimated_duration') }}" min="1" class="w-full border rounded px-3 py-2 @error('estimated_duration') border-red-500 @enderror">
                        @error('estimated_duration')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status (Readonly - dari Machine) -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <input type="text" name="status" id="status" value="active" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly>
                        <p class="text-xs text-gray-500 mt-1">Status terkunci (disetting di menu Machine)</p>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                        <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2 @error('assigned_to') border-red-500 @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
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
                        Create Schedule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeMachineSelect = document.getElementById('type_machine_id');
    const machineSelect = document.getElementById('machine_id');
    const maintenanceCategoryContainer = document.getElementById('maintenance_category_container');
    const maintenanceCategoryRadios = document.querySelectorAll('input[name="maintenance_category"]');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const maintenancePointsContainer = document.getElementById('maintenance_points_container');
    const maintenancePointsList = document.getElementById('maintenance_points_list');
    
    console.log('DOM loaded, elements:', {
        typeMachineSelect: !!typeMachineSelect,
        machineSelect: !!machineSelect,
        maintenanceCategoryContainer: !!maintenanceCategoryContainer,
        maintenanceCategoryRadios: maintenanceCategoryRadios.length,
        maintenancePointsContainer: !!maintenancePointsContainer,
        maintenancePointsList: !!maintenancePointsList
    });
    
    // Function to load machines by type
    function loadMachinesByType(typeId) {
        if (!typeId) {
            machineSelect.innerHTML = '<option value="">Select Machine</option>';
            return;
        }
        
        const url = `{{ route('preventive-maintenance.scheduling.get-machines-by-type') }}?type_id=${typeId}`;
        console.log('Fetching machines from:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                machineSelect.innerHTML = '<option value="">Select Machine</option>';
                
                if (data.machines && Array.isArray(data.machines)) {
                    if (data.machines.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Tidak ada mesin untuk type ini';
                        machineSelect.appendChild(option);
                    } else {
                        data.machines.forEach(machine => {
                            const option = document.createElement('option');
                            option.value = machine.id;
                            option.textContent = machine.name;
                            // Preserve old selection if exists
                            if (machineSelect.dataset.oldValue && machineSelect.dataset.oldValue == machine.id) {
                                option.selected = true;
                            }
                            machineSelect.appendChild(option);
                        });
                    }
                } else {
                    console.error('Invalid data format:', data);
                }
            })
            .catch(error => {
                console.error('Error fetching machines:', error);
                machineSelect.innerHTML = '<option value="">Select Machine</option>';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error loading machines';
                machineSelect.appendChild(option);
            });
    }
    
    // Filter machines by type
    if (typeMachineSelect) {
        typeMachineSelect.addEventListener('change', function() {
            const typeId = this.value;
            // Reset maintenance category
            maintenanceCategoryRadios.forEach(radio => {
                radio.checked = false;
            });
            
            // Reset maintenance points container
            if (maintenancePointsContainer) {
                maintenancePointsContainer.style.display = 'none';
                maintenancePointsList.innerHTML = '';
            }
            
            loadMachinesByType(typeId);
        });
        
        // Load machines on page load if type is already selected (e.g., from validation error)
        if (typeMachineSelect.value) {
            console.log('Type already selected on page load:', typeMachineSelect.value);
            // Preserve old machine selection if exists
            if (machineSelect.value) {
                machineSelect.dataset.oldValue = machineSelect.value;
            }
            loadMachinesByType(typeMachineSelect.value);
        }
    }
    
    // Get maintenance points by category and display them
    if (maintenanceCategoryRadios.length > 0) {
        maintenanceCategoryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const category = this.value;
                    const typeId = typeMachineSelect ? typeMachineSelect.value : '';
                    
                    console.log('Maintenance category changed:', { category, typeId });
                    
                    // Reset
                    maintenancePointsList.innerHTML = '';
                    maintenancePointsContainer.style.display = 'none';
                    
                    if (category && typeId) {
                        // Fetch all maintenance points for this category
                        const url = `{{ route('preventive-maintenance.scheduling.get-maintenance-points-by-category') }}?type_id=${typeId}&category=${category}`;
                        console.log('Fetching maintenance points from:', url);
                        
                        fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => {
                                console.log('Maintenance points response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Received maintenance points data:', data);
                                
                                if (data.maintenance_points && data.maintenance_points.length > 0) {
                                    // Display maintenance points as information only (read-only)
                                    maintenancePointsList.innerHTML = '';
                                    
                                    data.maintenance_points.forEach((point, index) => {
                                        const pointDiv = document.createElement('div');
                                        pointDiv.className = 'border rounded p-3 bg-white';
                                        
                                        let photoHtml = '';
                                        if (point.photo) {
                                            photoHtml = `<img src="${point.photo}" alt="Photo" class="w-12 h-12 object-cover rounded border float-left mr-3">`;
                                        }
                                        
                                        // Format frequency display
                                        let frequencyText = '-';
                                        if (point.frequency_type) {
                                            const freqTypeMap = {
                                                'daily': 'Harian',
                                                'weekly': 'Mingguan',
                                                'monthly': 'Bulanan',
                                                'quarterly': 'Triwulanan',
                                                'yearly': 'Tahunan',
                                                'custom': 'Custom'
                                            };
                                            const freqTypeLabel = freqTypeMap[point.frequency_type] || point.frequency_type;
                                            frequencyText = `${freqTypeLabel} (${point.frequency_value || 1}x)`;
                                        }
                                        
                                        pointDiv.innerHTML = `
                                            ${photoHtml}
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900">${point.name}</p>
                                                    ${point.instruction ? `<p class="text-sm text-gray-600 mt-1">${point.instruction}</p>` : ''}
                                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                        <span>Urutan: ${point.sequence}</span>
                                                        <span>•</span>
                                                        <span>Periode: ${frequencyText}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                        
                                        maintenancePointsList.appendChild(pointDiv);
                                    });
                                    
                                    maintenancePointsContainer.style.display = 'block';
                                } else {
                                    // No maintenance points found
                                    maintenancePointsList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Tidak ada maintenance point untuk kategori ini. Silakan buat maintenance point terlebih dahulu di Edit Machine Type.</p>';
                                    maintenancePointsContainer.style.display = 'block';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching maintenance points:', error);
                                maintenancePointsList.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading maintenance points</p>';
                                maintenancePointsContainer.style.display = 'block';
                            });
                    } else {
                        console.warn('Missing category or typeId:', { category, typeId });
                    }
                }
            });
        });
    }
    
    // Enable maintenance category radio buttons when machine is selected
    if (machineSelect && maintenanceCategoryContainer) {
        function updateMaintenanceCategoryState() {
            const machineId = machineSelect.value;
            const typeId = typeMachineSelect ? typeMachineSelect.value : '';
            const helpText = document.getElementById('maintenance_category_help');
            
            console.log('updateMaintenanceCategoryState called:', { machineId, typeId });
            
            if (machineId && typeId) {
                // Enable all radio buttons
                maintenanceCategoryRadios.forEach(radio => {
                    radio.disabled = false;
                    radio.style.pointerEvents = 'auto';
                    radio.style.opacity = '1';
                });
                
                // Enable container
                maintenanceCategoryContainer.style.opacity = '1';
                maintenanceCategoryContainer.style.pointerEvents = 'auto';
                
                // Enable all labels
                const labels = maintenanceCategoryContainer.querySelectorAll('label');
                labels.forEach(label => {
                    label.style.pointerEvents = 'auto';
                    label.style.opacity = '1';
                    label.style.cursor = 'pointer';
                    label.classList.remove('opacity-50', 'cursor-not-allowed');
                    label.classList.add('cursor-pointer');
                });
                
                if (helpText) {
                    helpText.textContent = 'Pilih salah satu tipe maintenance (AM/PM/PdM)';
                    helpText.classList.remove('text-gray-500');
                    helpText.classList.add('text-green-600');
                }
                
                console.log('Maintenance category radio buttons enabled');
            } else {
                // Disable if no machine or type selected
                maintenanceCategoryRadios.forEach(radio => {
                    radio.disabled = true;
                    radio.checked = false;
                    radio.style.pointerEvents = 'none';
                    radio.style.opacity = '0.6';
                });
                
                maintenanceCategoryContainer.style.opacity = '0.6';
                maintenanceCategoryContainer.style.pointerEvents = 'none';
                
                const labels = maintenanceCategoryContainer.querySelectorAll('label');
                labels.forEach(label => {
                    label.style.pointerEvents = 'none';
                    label.style.opacity = '0.6';
                    label.style.cursor = 'not-allowed';
                    label.classList.remove('cursor-pointer');
                    label.classList.add('opacity-50', 'cursor-not-allowed');
                });
                
                maintenancePointIdInput.value = '';
                frequencyTypeSelect.value = '';
                frequencyValueInput.value = 1;
                
                if (helpText) {
                    helpText.textContent = 'Pilih Machine terlebih dahulu untuk mengaktifkan pilihan ini';
                    helpText.classList.remove('text-green-600');
                    helpText.classList.add('text-gray-500');
                }
                
                console.log('Maintenance category radio buttons disabled');
            }
        }
        
        machineSelect.addEventListener('change', function() {
            console.log('Machine selected:', this.value);
            setTimeout(updateMaintenanceCategoryState, 100);
        });
        
        // Also listen to type machine change
        if (typeMachineSelect) {
            typeMachineSelect.addEventListener('change', function() {
                console.log('Type machine changed:', this.value);
                setTimeout(updateMaintenanceCategoryState, 100);
            });
        }
        
        // Check on page load
        setTimeout(updateMaintenanceCategoryState, 200);
    }
    
    // Auto-fill end date to end of year
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value) {
                const startDate = new Date(this.value);
                const currentYear = startDate.getFullYear();
                const endDate = new Date(currentYear, 11, 31); // December 31
                endDateInput.value = endDate.toISOString().split('T')[0];
            }
        });
    }

    // Machine search and barcode scan functionality
    const machines = @json($machines->map(function($machine) {
        return [
            'id' => $machine->id,
            'idMachine' => $machine->idMachine,
            'type_id' => $machine->type_id,
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
    const barcodeModal = document.createElement('div');
    barcodeModal.id = 'barcode_modal';
    barcodeModal.className = 'hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50';
    barcodeModal.innerHTML = `
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
    `;
    document.body.appendChild(barcodeModal);

    function filterMachinesByType() {
        const typeId = typeMachineSelect ? typeMachineSelect.value : '';
        if (!typeId) return machines;
        return machines.filter(m => m.type_id == typeId);
    }

    machineSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const filteredByType = filterMachinesByType();
        
        if (searchTerm.length === 0) {
            machineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = filteredByType.filter(m => 
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
        
        // Trigger change event to update maintenance category state
        machineId.dispatchEvent(new Event('change'));
    };

    clearMachine.addEventListener('click', function() {
        machineId.value = '';
        machineSearch.value = '';
        selectedMachine.classList.add('hidden');
        machineId.dispatchEvent(new Event('change'));
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
            document.getElementById('barcode_status').textContent = 'Barcode scanner tidak tersedia.';
            return;
        }
        
        try {
            const videoInputDevices = await codeReader.listVideoInputDevices();
            if (videoInputDevices.length === 0) {
                document.getElementById('barcode_status').textContent = 'Tidak ada kamera ditemukan.';
                return;
            }
            
            document.getElementById('barcode_status').textContent = 'Mengaktifkan kamera...';
            document.getElementById('start_barcode_btn').disabled = true;
            
            const selectedDeviceId = videoInputDevices[0].deviceId;
            const barcodeVideo = document.getElementById('barcode_video');
            
            codeReader.decodeFromVideoDevice(selectedDeviceId, 'barcode_video', (result, err) => {
                if (result) {
                    const scannedCode = result.getText();
                    document.getElementById('barcode_status').textContent = 'Barcode terdeteksi: ' + scannedCode;
                    
                    const filteredByType = filterMachinesByType();
                    const foundMachine = filteredByType.find(m => m.idMachine === scannedCode);
                    
                    if (foundMachine) {
                        const info = `${foundMachine.machineType} - (${foundMachine.plant} / ${foundMachine.process} / ${foundMachine.line})`;
                        selectMachine(foundMachine.id, foundMachine.idMachine, info);
                        stopBarcodeScanner();
                        barcodeModal.classList.add('hidden');
                    } else {
                        document.getElementById('barcode_status').textContent = 'Mesin dengan ID "' + scannedCode + '" tidak ditemukan atau tidak sesuai dengan Type Machine yang dipilih.';
                        setTimeout(() => {
                            document.getElementById('barcode_status').textContent = 'Scan barcode...';
                        }, 2000);
                    }
                }
                
                if (err && !(err instanceof ZXing.NotFoundException)) {
                    console.error('Barcode scan error:', err);
                }
            });
            
            document.getElementById('barcode_status').textContent = 'Arahkan kamera ke barcode...';
        } catch (error) {
            console.error('Error starting barcode scanner:', error);
            document.getElementById('barcode_status').textContent = 'Error: ' + error.message;
            document.getElementById('start_barcode_btn').disabled = false;
        }
    };

    window.stopBarcodeScanner = function() {
        if (codeReader) {
            codeReader.reset();
        }
        const barcodeVideo = document.getElementById('barcode_video');
        if (barcodeVideo && barcodeVideo.srcObject) {
            barcodeVideo.srcObject.getTracks().forEach(track => track.stop());
            barcodeVideo.srcObject = null;
        }
        document.getElementById('barcode_status').textContent = '';
        document.getElementById('start_barcode_btn').disabled = false;
    };

    barcodeModal.addEventListener('click', function(e) {
        if (e.target === barcodeModal) {
            stopBarcodeScanner();
            barcodeModal.classList.add('hidden');
        }
    });

    // Update machine dropdown when type machine changes
    if (typeMachineSelect) {
        typeMachineSelect.addEventListener('change', function() {
            machineSearch.value = '';
            machineId.value = '';
            selectedMachine.classList.add('hidden');
            machineDropdown.classList.add('hidden');
        });
    }

    // Initialize selected machine if exists
    @if(old('machine_id'))
        document.addEventListener('DOMContentLoaded', function() {
            const currentMachine = machines.find(m => m.id == {{ old('machine_id') }});
            if (currentMachine) {
                const info = `${currentMachine.machineType} - (${currentMachine.plant} / ${currentMachine.process} / ${currentMachine.line})`;
                selectMachine({{ old('machine_id') }}, currentMachine.idMachine, info);
            }
        });
    @endif
});
</script>
@endsection
