@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Preventive Maintenance Execution</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('preventive-maintenance.controlling.store') }}" method="POST" id="executionForm">
            @csrf
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Type Machine -->
                    <div>
                        <label for="type_machine_id" class="block text-sm font-medium text-gray-700 mb-2">Type Machine <span class="text-red-500">*</span></label>
                        <select name="type_machine_id" id="type_machine_id" class="w-full border rounded px-3 py-2 @error('type_machine_id') border-red-500 @enderror" required>
                            <option value="">Pilih Type Machine</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ old('type_machine_id', $selectedMachineTypeId ?? '') == $machineType->id ? 'selected' : '' }}>
                                    {{ $machineType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_machine_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Machine (ID Mesin) -->
                    <div>
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">ID Mesin <span class="text-red-500">*</span></label>
                        <select id="machine_id" class="w-full border rounded px-3 py-2 @error('machine_id') border-red-500 @enderror bg-gray-100" required disabled>
                            <option value="">Pilih Type Machine terlebih dahulu</option>
                        </select>
                        <input type="hidden" name="machine_id" id="machine_id_hidden" value="{{ old('machine_id') }}">
                        @error('machine_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Scheduled Date -->
                    <div>
                        <label for="scheduled_date" class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date <span class="text-red-500">*</span></label>
                        @php
                            $scheduledDateFromQuery = $selectedScheduledDate ?? old('scheduled_date');
                            $isDateLocked = isset($selectedScheduledDate) && $selectedScheduledDate;
                        @endphp
                        <input type="date" 
                               name="scheduled_date" 
                               id="scheduled_date" 
                               value="{{ $scheduledDateFromQuery ?? now()->toDateString() }}" 
                               class="w-full border rounded px-3 py-2 @error('scheduled_date') border-red-500 @enderror {{ $isDateLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}" 
                               {{ $isDateLocked ? 'readonly' : '' }}
                               required>
                        @if($isDateLocked)
                            <p class="text-xs text-gray-500 mt-1">Tanggal terkunci sesuai schedule yang dipilih</p>
                        @endif
                        @error('scheduled_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror" required>
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="skipped" {{ old('status') == 'skipped' ? 'selected' : '' }}>Skipped</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- PIC (Performed By) -->
                    <div class="md:col-span-2">
                        <label for="performed_by" class="block text-sm font-medium text-gray-700 mb-2">PIC (Performed By) <span class="text-gray-500 text-xs">(Otomatis dari Schedule)</span></label>
                        <select name="performed_by" id="performed_by" class="w-full border rounded px-3 py-2 bg-gray-100 @error('performed_by') border-red-500 @enderror" readonly disabled>
                            <option value="">Pilih Machine dan klik Show terlebih dahulu</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('performed_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="performed_by" id="performed_by_hidden" value="{{ old('performed_by') }}">
                        @error('performed_by')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Show Button -->
                    <div class="md:col-span-2">
                        <button type="button" id="showMaintenancePointsBtn" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center justify-center disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Show Maintenance Points
                        </button>
                    </div>
                </div>
                
                <!-- Maintenance Points List -->
                <div id="maintenancePointsContainer" class="mt-6 hidden">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Maintenance Points</h3>
                        
                        <!-- Batch Update Section -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="selectAllPointsCreate" onchange="toggleSelectAllPointsCreate()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="selectAllPointsCreate" class="text-sm font-medium text-gray-700">Select All</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="batchStatusCreate" class="text-sm font-medium text-gray-700">Update Status:</label>
                                <select id="batchStatusCreate" class="border rounded px-3 py-1 text-sm">
                                    <option value="">Pilih Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="skipped">Skipped</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <button type="button" onclick="batchUpdatePointsStatus()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded text-sm transition flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Update Selected
                            </button>
                        </div>
                    </div>
                    <div id="maintenancePointsList" class="space-y-3">
                        <!-- Maintenance points will be loaded here -->
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('preventive-maintenance.controlling.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        Save Executions
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
    const machineIdHidden = document.getElementById('machine_id_hidden');
    const scheduledDateInput = document.getElementById('scheduled_date');
    const showBtn = document.getElementById('showMaintenancePointsBtn');
    const maintenancePointsContainer = document.getElementById('maintenancePointsContainer');
    const maintenancePointsList = document.getElementById('maintenancePointsList');
    const submitBtn = document.getElementById('submitBtn');
    const executionForm = document.getElementById('executionForm');
    
    // Load machines by type
    function loadMachinesByType(typeId) {
        return new Promise((resolve, reject) => {
            if (!typeId) {
                machineSelect.innerHTML = '<option value="">Pilih Type Machine terlebih dahulu</option>';
                machineSelect.disabled = true;
                machineSelect.classList.add('bg-gray-100');
                machineIdHidden.value = '';
                updateShowButtonState();
                resolve();
                return;
            }
            
            const url = `{{ route('preventive-maintenance.controlling.get-machines-by-type') }}?type_id=${typeId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    machineSelect.innerHTML = '<option value="">Pilih ID Mesin</option>';
                    
                    if (data.machines && data.machines.length > 0) {
                        data.machines.forEach(machine => {
                            const option = document.createElement('option');
                            option.value = machine.id;
                            option.textContent = machine.name;
                            machineSelect.appendChild(option);
                        });
                        machineSelect.disabled = false;
                        machineSelect.classList.remove('bg-gray-100');
                    } else {
                        machineSelect.innerHTML = '<option value="">Tidak ada mesin untuk type ini</option>';
                        machineSelect.disabled = true;
                        machineSelect.classList.add('bg-gray-100');
                    }
                    
                    machineIdHidden.value = '';
                    updateShowButtonState();
                    resolve();
                })
                .catch(error => {
                    console.error('Error loading machines:', error);
                    machineSelect.innerHTML = '<option value="">Error loading machines</option>';
                    machineSelect.disabled = true;
                    machineSelect.classList.add('bg-gray-100');
                    reject(error);
                });
        });
    }
    
    // Update show button state
    function updateShowButtonState() {
        const machineId = machineSelect.value || machineIdHidden.value;
        const scheduledDate = scheduledDateInput.value;
        
        if (machineId && scheduledDate) {
            showBtn.disabled = false;
        } else {
            showBtn.disabled = true;
        }
    }
    
    // Show maintenance points
    function showMaintenancePoints() {
        const machineId = machineSelect.value || machineIdHidden.value;
        const scheduledDate = scheduledDateInput.value;
        
        if (!machineId || !scheduledDate) {
            alert('Pilih Machine dan Scheduled Date terlebih dahulu');
            return;
        }
        
        const url = `{{ route('preventive-maintenance.controlling.get-maintenance-points-by-machine-and-date') }}?machine_id=${machineId}&scheduled_date=${scheduledDate}`;
        
        showBtn.disabled = true;
        showBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>Loading...';
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                showBtn.disabled = false;
                showBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Show Maintenance Points
                `;
                
                // Auto-fill and lock PIC
                const performedBySelect = document.getElementById('performed_by');
                const performedByHidden = document.getElementById('performed_by_hidden');
                
                if (data.pic_id) {
                    performedBySelect.value = data.pic_id;
                    performedByHidden.value = data.pic_id;
                    performedBySelect.disabled = true;
                    performedBySelect.classList.add('bg-gray-100');
                } else {
                    performedBySelect.value = '';
                    performedByHidden.value = '';
                    performedBySelect.disabled = true;
                    performedBySelect.classList.add('bg-gray-100');
                }
                
                maintenancePointsList.innerHTML = '';
                
                if (data.maintenance_points && data.maintenance_points.length > 0) {
                    data.maintenance_points.forEach((point, index) => {
                        const pointDiv = document.createElement('div');
                        pointDiv.className = 'border rounded p-4 bg-white';
                        
                        let photoHtml = '';
                        if (point.photo) {
                            photoHtml = `<img src="${point.photo}" alt="Photo" class="w-16 h-16 object-cover rounded border float-left mr-3">`;
                        }
                        
                        const currentStatus = point.execution_status || 'pending';
                        
                        const overdueBadge = point.is_overdue ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 ml-2">Terlewat (${point.original_start_date})</span>` : '';
                        
                        pointDiv.innerHTML = `
                            <div class="flex items-start gap-3">
                                <input type="checkbox" class="point-checkbox-create rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1" 
                                       data-index="${index}">
                                ${photoHtml}
                                <div class="flex-1 ${point.photo ? 'ml-0' : ''}">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <p class="font-semibold text-gray-900">${point.maintenance_point_name}</p>
                                                ${overdueBadge}
                                            </div>
                                            ${point.instruction ? `<p class="text-sm text-gray-600 mt-1">${point.instruction}</p>` : ''}
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                        <select name="executions[${index}][status]" class="w-full border rounded px-3 py-2 execution-status" data-index="${index}" required>
                                            <option value="pending" ${currentStatus == 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="in_progress" ${currentStatus == 'in_progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="completed" ${currentStatus == 'completed' ? 'selected' : ''}>Completed</option>
                                            <option value="skipped" ${currentStatus == 'skipped' ? 'selected' : ''}>Skipped</option>
                                            <option value="cancelled" ${currentStatus == 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="executions[${index}][schedule_id]" value="${point.schedule_id}">
                                        ${point.execution_id ? `<input type="hidden" name="executions[${index}][execution_id]" value="${point.execution_id}">` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="clear-both"></div>
                        `;
                        
                        maintenancePointsList.appendChild(pointDiv);
                    });
                    
                    maintenancePointsContainer.classList.remove('hidden');
                    submitBtn.disabled = false;
                } else {
                    maintenancePointsList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Tidak ada maintenance point untuk mesin ini pada tanggal yang dipilih.</p>';
                    maintenancePointsContainer.classList.remove('hidden');
                    submitBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading maintenance points:', error);
                showBtn.disabled = false;
                showBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Show Maintenance Points
                `;
                alert('Error loading maintenance points');
            });
    }
    
    // Event listeners
    typeMachineSelect.addEventListener('change', function() {
        loadMachinesByType(this.value);
        
        // Reset PIC
        const performedBySelect = document.getElementById('performed_by');
        const performedByHidden = document.getElementById('performed_by_hidden');
        performedBySelect.value = '';
        performedByHidden.value = '';
        performedBySelect.disabled = true;
        performedBySelect.classList.add('bg-gray-100');
        
        maintenancePointsContainer.classList.add('hidden');
        submitBtn.disabled = true;
    });
    
    machineSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        machineIdHidden.value = selectedValue;
        // Also update the select's required attribute based on hidden input
        if (selectedValue) {
            machineIdHidden.setAttribute('required', 'required');
        } else {
            machineIdHidden.removeAttribute('required');
        }
        
        // Reset PIC
        const performedBySelect = document.getElementById('performed_by');
        const performedByHidden = document.getElementById('performed_by_hidden');
        performedBySelect.value = '';
        performedByHidden.value = '';
        performedBySelect.disabled = true;
        performedBySelect.classList.add('bg-gray-100');
        
        updateShowButtonState();
        maintenancePointsContainer.classList.add('hidden');
        submitBtn.disabled = true;
    });
    
    scheduledDateInput.addEventListener('change', function() {
        // Don't reset if date is locked (readonly)
        if (this.readOnly) {
            return;
        }
        
        // Reset PIC when date changes
        const performedBySelect = document.getElementById('performed_by');
        const performedByHidden = document.getElementById('performed_by_hidden');
        performedBySelect.value = '';
        performedByHidden.value = '';
        performedBySelect.disabled = true;
        performedBySelect.classList.add('bg-gray-100');
        
        updateShowButtonState();
        maintenancePointsContainer.classList.add('hidden');
        submitBtn.disabled = true;
    });
    
    showBtn.addEventListener('click', showMaintenancePoints);
    
    // Load machines on page load if type is already selected
    if (typeMachineSelect.value) {
        loadMachinesByType(typeMachineSelect.value).then(() => {
            // Pre-select machine if provided
            @if(isset($selectedMachineId))
                const selectedMachineId = {{ $selectedMachineId }};
                if (selectedMachineId) {
                    machineSelect.value = selectedMachineId;
                    machineIdHidden.value = selectedMachineId;
                    updateShowButtonState();
                }
            @endif
        });
    }
    
    // Prevent form auto-submit on Enter key
    executionForm.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            // Allow Enter in textarea
            if (e.target.tagName === 'TEXTAREA') {
                return true;
            }
            // Prevent form submission on Enter for other inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Prevent accidental form submission - only allow submit via button click
    let formSubmitted = false;
    submitBtn.addEventListener('click', function(e) {
        formSubmitted = true;
    });
    
    executionForm.addEventListener('submit', function(e) {
        // If form was not submitted via button, prevent submission
        if (!formSubmitted) {
            e.preventDefault();
            return false;
        }
        
        // Validate that maintenance points are loaded
        if (maintenancePointsList.children.length === 0) {
            e.preventDefault();
            alert('Silakan klik "Show Maintenance Points" terlebih dahulu');
            return false;
        }
        
        // Reset flag
        formSubmitted = false;
    });
});

function toggleSelectAllPointsCreate() {
    const selectAll = document.getElementById('selectAllPointsCreate');
    const checkboxes = document.querySelectorAll('.point-checkbox-create');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

function batchUpdatePointsStatus() {
    const selectedStatus = document.getElementById('batchStatusCreate').value;
    if (!selectedStatus) {
        alert('Pilih status terlebih dahulu');
        return;
    }
    
    const checkedBoxes = document.querySelectorAll('.point-checkbox-create:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu maintenance point');
        return;
    }
    
    // Update status for selected points
    checkedBoxes.forEach(checkbox => {
        const index = checkbox.dataset.index;
        const statusSelect = document.querySelector(`select.execution-status[data-index="${index}"]`);
        if (statusSelect) {
            statusSelect.value = selectedStatus;
        }
    });
    
    // Uncheck all checkboxes after update
    checkedBoxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllPointsCreate').checked = false;
    document.getElementById('batchStatusCreate').value = '';
    
    alert(`Berhasil mengupdate status ${checkedBoxes.length} maintenance point menjadi "${selectedStatus}"`);
}
</script>
@endsection
