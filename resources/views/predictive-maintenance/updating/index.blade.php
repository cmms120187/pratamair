@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Predictive Maintenance - Updating</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Tanggal Hari Ini: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }} | 
                    <span class="text-blue-600 font-medium">Menampilkan jadwal tahun {{ \Carbon\Carbon::now()->year }}</span>
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total to Update</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal to Update Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Jadwal to Update</h3>
            @if($jadwalPaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jadwalPaginator as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $jadwal['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $jadwal['machine']->machineType->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $jadwal['machine']->plant_name ?? '-' }} / {{ $jadwal['machine']->line_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                        {{ \Carbon\Carbon::parse($jadwal['scheduled_date'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($jadwal['status'] == 'pending')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                    @elseif($jadwal['status'] == 'in_progress')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($jadwal['status']) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $firstExecution = $jadwal['executions'][0] ?? null;
                                        $firstSchedule = $jadwal['first_schedule'] ?? null;
                                    @endphp
                                    <div class="flex items-center justify-center gap-2">
                                    @if($firstExecution)
                                        <a href="{{ route('predictive-maintenance.updating.edit', $firstExecution->id) }}"
                                               class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded shadow transition text-sm"
                                               title="Update Jadwal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Update
                                            </a>
                                        @elseif($firstSchedule)
                                            <a href="{{ route('predictive-maintenance.updating.create-from-schedule', ['schedule_id' => $firstSchedule->id, 'scheduled_date' => $jadwal['scheduled_date']]) }}"
                                               class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded shadow transition text-sm"
                                               title="Buat dan Update Jadwal">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                                Update
                                        </a>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $jadwalPaginator->links() }}
                </div>
            @else
                @php
                    $user = auth()->user();
                    $userRole = $user->role ?? 'mekanik';
                @endphp
                @if($userRole === 'team_leader')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-yellow-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-gray-700 font-medium mb-2">Tidak ada jadwal yang di-assign ke Anda</p>
                        <p class="text-sm text-gray-600 mb-4">
                            Saat ini tidak ada jadwal Predictive Maintenance yang di-assign ke akun Anda ({{ $user->name }} - NIK: {{ $user->nik ?? '-' }}).
                        </p>
                        <p class="text-sm text-gray-600">
                            Silakan hubungi administrator untuk mengassign jadwal di menu <strong>Predictive Maintenance > Scheduling</strong> dengan memilih Anda sebagai PIC (Person In Charge).
                        </p>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No jadwal to update.</p>
                @endif
            @endif
        </div>

        <!-- Completed Jadwal (Information Only) -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Completed Jadwal <span class="text-sm font-normal text-gray-500">(Information Only)</span></h3>
            @if($completedJadwalPaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">End Time</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($completedJadwalPaginator as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $jadwal['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $jadwal['machine']->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($jadwal['scheduled_date'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $jadwal['latest_end_time'] ? \Carbon\Carbon::parse($jadwal['latest_end_time'])->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $status = $jadwal['status'] ?? ($jadwal['executions'][0]->status ?? 'completed');
                                    @endphp
                                    @if($status == 'completed')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                    @elseif($status == 'in_progress')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $firstExecution = $jadwal['executions'][0] ?? null;
                                    @endphp
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="showMaintenancePoints({{ $jadwal['machine_id'] }}, '{{ $jadwal['scheduled_date'] }}')"
                                                class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded shadow transition text-sm"
                                                title="Lihat dan Edit Maintenance Points">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Points
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($stats['completed'] > 20)
                    <p class="text-sm text-gray-500 text-center mt-4">Menampilkan 20 jadwal terbaru dari total {{ $stats['completed'] }} completed executions.</p>
                @endif
                <div class="mt-4">
                    {{ $completedJadwalPaginator->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No completed jadwal.</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Maintenance Points -->
<div id="maintenancePointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center" onclick="if(event.target === this) closeMaintenancePointsModal();">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="if(event.target === this) event.stopPropagation();">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Maintenance Points</h3>
                <button onclick="closeMaintenancePointsModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="maintenancePointsContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showMaintenancePoints(machineId, scheduledDate) {
    const modal = document.getElementById('maintenancePointsModal');
    const content = document.getElementById('maintenancePointsContent');
    
    // Store machineId and scheduledDate in modal dataset for later use
    modal.dataset.machineId = machineId;
    modal.dataset.scheduledDate = scheduledDate;

    content.innerHTML = '<p class="text-center py-4">Loading...</p>';
    modal.classList.remove('hidden');

    const url = `{{ route('predictive-maintenance.updating.get-maintenance-points-by-machine-and-date') }}?machine_id=${machineId}&scheduled_date=${scheduledDate}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.maintenance_points && data.maintenance_points.length > 0) {
            let html = '';
            data.maintenance_points.forEach((point, index) => {
                const statusClass = point.status === 'completed' ? 'bg-green-100 text-green-800' :
                                   point.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-gray-100 text-gray-800';

                // Measurement status badge
                let measurementStatusBadge = '';
                if (point.measurement_status) {
                    const statusColors = {
                        'normal': 'bg-green-100 text-green-800',
                        'warning': 'bg-yellow-100 text-yellow-800',
                        'critical': 'bg-red-100 text-red-800'
                    };
                    const statusText = {
                        'normal': 'Normal',
                        'warning': 'Warning',
                        'critical': 'Critical'
                    };
                    measurementStatusBadge = `<span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full ${statusColors[point.measurement_status] || 'bg-gray-100 text-gray-800'}">${statusText[point.measurement_status] || point.measurement_status}</span>`;
                }

                let photoHtml = '';
                if (point.photo) {
                    photoHtml = `<img src="${point.photo}" alt="Photo" class="w-16 h-16 object-cover rounded border float-left mr-3">`;
                }

                html += `
                    <div class="border rounded p-4 bg-white">
                        <div class="flex items-start gap-3">
                            ${photoHtml}
                            <div class="flex-1 ${point.photo ? 'ml-0' : ''}">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">${point.maintenance_point_name}</h4>
                                        ${point.standard_name && point.standard_name !== '-' ? `
                                            <div class="mt-1 text-sm text-blue-600">
                                                <strong>Standard:</strong> ${point.standard_name}
                                                ${point.standard_min !== null && point.standard_max !== null ? ` (${point.standard_min} - ${point.standard_max} ${point.standard_unit || ''})` : ''}
                                            </div>
                                        ` : ''}
                                        ${point.instruction ? `<p class="text-sm text-gray-600 mt-1">${point.instruction}</p>` : ''}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${point.status}</span>
                                        ${measurementStatusBadge}
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mt-3">
                                    <div><strong>Measured Value:</strong> ${point.measured_value !== null ? point.measured_value + ' ' + (point.standard_unit || '') : '-'}</div>
                                    <div><strong>Performed By:</strong> ${point.performed_by || '-'}</div>
                                    <div><strong>Start Time:</strong> ${point.actual_start_time || '-'}</div>
                                    <div><strong>End Time:</strong> ${point.actual_end_time || '-'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            content.innerHTML = html;
            
            // Re-initialize input fields after innerHTML to ensure they're interactive
            setTimeout(() => {
                data.maintenance_points.forEach((point) => {
                    if (point.execution_id) {
                        const inputField = document.getElementById(`measured_value-${point.execution_id}`);
                        if (inputField) {
                            // Ensure it's interactive
                            inputField.disabled = false;
                            inputField.readOnly = false;
                            inputField.removeAttribute('readonly');
                            inputField.removeAttribute('disabled');
                            inputField.style.pointerEvents = 'auto';
                            inputField.style.cursor = 'text';
                            inputField.style.zIndex = '1000';
                            inputField.style.position = 'relative';
                            
                            console.log('Input field re-initialized:', inputField.id, 'disabled:', inputField.disabled, 'readonly:', inputField.readOnly);
                        }
                    }
                });
            }, 100);
        } else {
            content.innerHTML = '<p class="text-center py-4 text-gray-500">No maintenance points found for this date.</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center py-4 text-red-500">Error loading maintenance points.</p>';
    });
}

function closeMaintenancePointsModal() {
    document.getElementById('maintenancePointsModal').classList.add('hidden');
    // Close all edit forms when closing modal
    document.querySelectorAll('[id^="editForm-"]').forEach(form => {
        form.classList.add('hidden');
    });
}

document.getElementById('maintenancePointsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMaintenancePointsModal();
    }
});

// Show edit form inline
function showEditForm(executionId) {
    console.log('showEditForm called with executionId:', executionId);
    
    // Hide all other edit forms first
    document.querySelectorAll('[id^="editForm-"]').forEach(form => {
        if (form.id !== `editForm-${executionId}`) {
            form.classList.add('hidden');
        }
    });
    
    const editForm = document.getElementById(`editForm-${executionId}`);
    console.log('editForm element:', editForm);
    
    if (editForm) {
        // Remove hidden class to show the form
        editForm.classList.remove('hidden');
        // Force display style to ensure form is visible
        editForm.style.display = 'block';
        editForm.style.visibility = 'visible';
        editForm.style.opacity = '1';
        
        // Ensure form and all inputs are interactive
        const allInputs = editForm.querySelectorAll('input, select, textarea, button');
        allInputs.forEach(input => {
            input.disabled = false;
            input.readOnly = false;
            input.style.pointerEvents = 'auto';
            input.style.cursor = 'text';
        });
        
        // Scroll to form
        setTimeout(() => {
            editForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Get input field
            const inputField = document.getElementById(`measured_value-${executionId}`);
            console.log('Input field:', inputField);
            
            if (inputField) {
                // Ensure input is not disabled or readonly
                inputField.disabled = false;
                inputField.readOnly = false;
                inputField.removeAttribute('readonly');
                inputField.removeAttribute('disabled');
                inputField.style.pointerEvents = 'auto';
                inputField.style.cursor = 'text';
                inputField.style.userSelect = 'text';
                inputField.style.webkitUserSelect = 'text';
                
                // Clone input to remove any interfering event listeners
                const newInput = inputField.cloneNode(true);
                inputField.parentNode.replaceChild(newInput, inputField);
                
                // Get the clean input
                const cleanInput = document.getElementById(`measured_value-${executionId}`);
                
                // Ensure it's fully interactive - no blocking
                cleanInput.disabled = false;
                cleanInput.readOnly = false;
                cleanInput.removeAttribute('readonly');
                cleanInput.removeAttribute('disabled');
                cleanInput.style.pointerEvents = 'auto';
                cleanInput.style.cursor = 'text';
                cleanInput.style.userSelect = 'text';
                cleanInput.style.webkitUserSelect = 'text';
                cleanInput.style.zIndex = '1000';
                cleanInput.style.position = 'relative';
                
                // Only add passive listeners for debugging
                cleanInput.addEventListener('input', function(e) {
                    console.log('✅ Input field value changed to:', this.value);
                }, { passive: true });
                
                cleanInput.addEventListener('keydown', function(e) {
                    console.log('✅ Key pressed:', e.key);
                }, { passive: true });
                
                console.log('✅ Input field cleaned and ready:', cleanInput.id);
                
                // Don't auto-focus, let user click manually
                // Just ensure the field is ready for input
                console.log('Input field is ready for input');
                console.log('Input field value:', inputField.value);
                console.log('Input field disabled:', inputField.disabled);
                console.log('Input field readonly:', inputField.readOnly);
            } else {
                console.error('Input field not found for executionId:', executionId);
            }
        }, 100);
    } else {
        console.error('Edit form not found for executionId:', executionId);
        alert('Form edit tidak ditemukan. Silakan refresh halaman dan coba lagi.');
    }
}

// Hide edit form inline
function hideEditForm(executionId) {
    const editForm = document.getElementById(`editForm-${executionId}`);
    if (editForm) {
        editForm.classList.add('hidden');
    }
}

// Update execution via AJAX
function updateExecution(event, executionId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    let measuredValue = formData.get('measured_value');
    
    // Convert to number if it's a valid number
    if (measuredValue) {
        measuredValue = measuredValue.toString().trim();
        const numValue = parseFloat(measuredValue);
        if (isNaN(numValue)) {
            alert('Nilai harus berupa angka yang valid.');
            return false;
        }
        // Update formData with the numeric value
        formData.set('measured_value', numValue);
    }
    
    const status = formData.get('status');
    
    // Validation: If status is completed, measured_value must be filled
    if (status === 'completed' && (!measuredValue || measuredValue.toString().trim() === '')) {
        alert('Measured value harus diisi jika status adalah Completed.');
        return false;
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Updating...';
    
    const url = `{{ url('predictive-maintenance/updating') }}/${executionId}`;
    const csrfToken = form.querySelector('input[name="_token"]').value;
    
    // Ensure _method is set to PUT for method spoofing
    formData.append('_method', 'PUT');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Terjadi kesalahan saat mengupdate data.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success || data.message) {
            // Close edit form
            hideEditForm(executionId);
            
            // Reload maintenance points to show updated data
            const modal = document.getElementById('maintenancePointsModal');
            const machineId = modal.dataset.machineId;
            const scheduledDate = modal.dataset.scheduledDate;
            
            if (machineId && scheduledDate) {
                showMaintenancePoints(machineId, scheduledDate);
            }
            
            // Show success message
            alert('Data berhasil diupdate!');
        } else {
            alert('Terjadi kesalahan saat mengupdate data.');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Terjadi kesalahan saat mengupdate data.');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
    
    return false;
}
</script>
@endsection
