@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                @if(isset($singlePoint) && $singlePoint)
                    Edit Single Maintenance Point
                @else
                    Update Predictive Maintenance Execution
                @endif
            </h1>
            <a href="{{ route('predictive-maintenance.updating.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($singlePoint) && $singlePoint)
            <!-- Single Point Mode: Show current data that will be replaced -->
            @if(count($maintenancePoints) > 0)
                @php
                    $currentPoint = $maintenancePoints[0];
                @endphp
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-2">Data Sebelumnya (Akan Diganti):</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><strong>Point:</strong> {{ $currentPoint['maintenance_point_name'] }}</div>
                        <div><strong>Status:</strong> <span class="px-2 py-1 text-xs rounded-full 
                            @if($currentPoint['execution_status'] == 'completed') bg-green-100 text-green-800
                            @elseif($currentPoint['execution_status'] == 'in_progress') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">{{ ucfirst($currentPoint['execution_status'] ?? 'pending') }}</span></div>
                        <div><strong>Measured Value:</strong> {{ $currentPoint['measured_value'] ?? '-' }} {{ $currentPoint['standard_unit'] ?? '' }}</div>
                        <div><strong>Performed By:</strong> {{ $execution->performedBy->name ?? '-' }}</div>
                        @if($currentPoint['actual_start_time'])
                            <div><strong>Start Time:</strong> {{ \Carbon\Carbon::parse($currentPoint['actual_start_time'])->format('d/m/Y H:i') }}</div>
                        @endif
                        @if($currentPoint['actual_end_time'])
                            <div><strong>End Time:</strong> {{ \Carbon\Carbon::parse($currentPoint['actual_end_time'])->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <form action="{{ (isset($singlePoint) && $singlePoint) ? route('predictive-maintenance.updating.update', $execution->id) : route('predictive-maintenance.updating.batch-update') }}" method="POST" id="executionForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            @if(!isset($singlePoint) || !$singlePoint)
                <input type="hidden" name="machine_id" value="{{ $machine->id }}">
                <input type="hidden" name="scheduled_date" value="{{ $scheduledDate }}">
            @endif

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Execution Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Machine Info -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                        <div class="border rounded px-3 py-2 bg-gray-50">
                            <div class="font-semibold text-gray-900">{{ $machine->idMachine ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $machine->machineType->name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $machine->plant_name ?? '-' }} / {{ $machine->line_name ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date</label>
                        <div class="border rounded px-3 py-2 bg-gray-50">
                            {{ $scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('d/m/Y') : '-' }}
                        </div>
                    </div>

                    <!-- PIC (Performed By) -->
                    <div>
                        <label for="performed_by" class="block text-sm font-medium text-gray-700 mb-2">PIC (Performed By)</label>
                        <select name="performed_by" id="performed_by" class="w-full border rounded px-3 py-2 @error('performed_by') border-red-500 @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('performed_by', $picId) == $user->id ? 'selected' : '' }}>
                                    @if($user->nik){{ $user->nik }} - @endif{{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('performed_by')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Maintenance Points List -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        @if(isset($singlePoint) && $singlePoint)
                            Maintenance Point
                        @else
                            Maintenance Points (Total: {{ count($maintenancePoints) }})
                        @endif
                    </h3>
                </div>
                
                @php
                    \Log::info('View edit - Maintenance points count: ' . count($maintenancePoints));
                    \Log::info('View edit - Machine ID: ' . ($machine->id ?? 'N/A'));
                    \Log::info('View edit - Scheduled Date: ' . ($scheduledDate ?? 'N/A'));
                    \Log::info('View edit - Single Point: ' . (isset($singlePoint) && $singlePoint ? 'Yes' : 'No'));
                @endphp
                
                @if(count($maintenancePoints) > 0)
                    <div class="space-y-4">
                        @foreach($maintenancePoints as $index => $point)
                            <div class="border rounded p-4 bg-white">
                                <div class="flex items-start gap-3">
                                    @if($point['photo'])
                                        <img src="{{ $point['photo'] }}" alt="Photo" class="w-16 h-16 object-cover rounded border float-left mr-3">
                                    @endif
                                    <div class="flex-1 {{ $point['photo'] ? 'ml-0' : '' }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <p class="font-semibold text-gray-900">{{ $point['maintenance_point_name'] }}</p>
                                                    @if($point['measurement_status'])
                                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                                            @if($point['measurement_status'] == 'normal') bg-green-100 text-green-800
                                                            @elseif($point['measurement_status'] == 'warning') bg-yellow-100 text-yellow-800
                                                            @else bg-red-100 text-red-800
                                                            @endif">
                                                            {{ ucfirst($point['measurement_status']) }}
                                                        </span>
                                                    @endif
            </div>

                                                @if($point['standard_name'] && $point['standard_name'] !== '-')
                                                    <div class="mt-2 p-2 bg-blue-50 rounded border border-blue-200">
                                                        <p class="text-xs font-semibold text-blue-900">Standard: {{ $point['standard_name'] }}</p>
                                                        <p class="text-xs text-blue-700">
                                                            Range: {{ $point['standard_min'] !== null ? $point['standard_min'] : '-' }} - {{ $point['standard_max'] !== null ? $point['standard_max'] : '-' }} {{ $point['standard_unit'] ?? '' }}
                                                        </p>
                                                        @if($point['standard_target'] !== null)
                                                            <p class="text-xs text-blue-700">Target: {{ $point['standard_target'] }} {{ $point['standard_unit'] ?? '' }}</p>
                                                        @endif
                    </div>
                                                @endif
                                                
                                                @if($point['instruction'])
                                                    <p class="text-sm text-gray-600 mt-1">{{ $point['instruction'] }}</p>
                                                @endif
                                            </div>
                    </div>

                                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Measured Value 
                                                    <span class="text-gray-500 text-xs">({{ $point['standard_unit'] ?? '' }})</span>
                                                    <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(Wajib untuk Completed)</span>
                                                </label>
                                                <input type="number"
                                                       step="0.01"
                                                       name="{{ (isset($singlePoint) && $singlePoint) ? 'measured_value' : 'executions[' . $index . '][measured_value]' }}"
                                                       class="w-full border rounded px-3 py-2 measured-value-input"
                                                       data-index="{{ $index }}"
                                                       data-min="{{ $point['standard_min'] ?? '' }}"
                                                       data-max="{{ $point['standard_max'] ?? '' }}"
                                                       data-target="{{ $point['standard_target'] ?? '' }}"
                                                       value="{{ old((isset($singlePoint) && $singlePoint) ? 'measured_value' : "executions.{$index}.measured_value", $point['measured_value']) }}"
                                                       placeholder="Masukkan nilai pengukuran"
                                                       oninput="checkCompletedStatusForPoint({{ $index }})">
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Range: {{ $point['standard_min'] !== null ? $point['standard_min'] : '-' }} - {{ $point['standard_max'] !== null ? $point['standard_max'] : '-' }}
                                                </p>
                    </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                                                <select name="{{ (isset($singlePoint) && $singlePoint) ? 'status' : 'executions[' . $index . '][status]' }}" 
                                                        class="w-full border rounded px-3 py-2 execution-status" 
                                                        data-index="{{ $index }}" 
                                                        required
                                                        onchange="checkCompletedStatusForPoint({{ $index }})">
                                                    <option value="pending" {{ old((isset($singlePoint) && $singlePoint) ? 'status' : "executions.{$index}.status", $point['execution_status']) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ old((isset($singlePoint) && $singlePoint) ? 'status' : "executions.{$index}.status", $point['execution_status']) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="completed" id="status-completed-option-{{ $index }}" {{ old((isset($singlePoint) && $singlePoint) ? 'status' : "executions.{$index}.status", $point['execution_status']) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="skipped" {{ old((isset($singlePoint) && $singlePoint) ? 'status' : "executions.{$index}.status", $point['execution_status']) == 'skipped' ? 'selected' : '' }}>Skipped</option>
                                                    <option value="cancelled" {{ old((isset($singlePoint) && $singlePoint) ? 'status' : "executions.{$index}.status", $point['execution_status']) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                <p id="status-warning-{{ $index }}" class="text-xs text-yellow-600 mt-1 hidden">
                                                    ⚠️ Measured value harus diisi sebelum memilih status Completed.
                                                </p>
                </div>
            </div>

                                        @if(!isset($singlePoint) || !$singlePoint)
                                            <input type="hidden" name="executions[{{ $index }}][schedule_id]" value="{{ $point['schedule_id'] }}">
                                            @if($point['execution_id'])
                                                <input type="hidden" name="executions[{{ $index }}][execution_id]" value="{{ $point['execution_id'] }}">
                                            @endif
                                        @endif
                            </div>
                    </div>
                                <div class="clear-both"></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">Tidak ada maintenance point untuk mesin ini pada tanggal yang dipilih.</p>
                @endif
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('predictive-maintenance.updating.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Update All Executions
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Check if measured_value is filled before allowing completed status for each point
function checkCompletedStatusForPoint(index) {
    const measuredValueInput = document.querySelector(`input[name="executions[${index}][measured_value]"]`);
    const statusSelect = document.querySelector(`select[name="executions[${index}][status]"]`);
    const statusWarning = document.getElementById(`status-warning-${index}`);
    const completedOption = document.getElementById(`status-completed-option-${index}`);
    
    const measuredValue = measuredValueInput ? measuredValueInput.value : '';
    const status = statusSelect ? statusSelect.value : '';
    
    if (status === 'completed') {
        if (!measuredValue || measuredValue.trim() === '') {
            if (statusWarning) statusWarning.classList.remove('hidden');
            if (completedOption) completedOption.disabled = false; // Allow selection but show warning
        } else {
            if (statusWarning) statusWarning.classList.add('hidden');
        }
    } else {
        if (statusWarning) statusWarning.classList.add('hidden');
    }
    
    // Disable completed option if measured_value is empty
    if (completedOption) {
        if (!measuredValue || measuredValue.trim() === '') {
            // Don't disable, but show warning when selected
        } else {
            completedOption.disabled = false;
        }
    }
}

// Prevent form submission if any point has completed status but no measured_value
document.getElementById('executionForm').addEventListener('submit', function(e) {
    let hasError = false;
    
    @if(isset($singlePoint) && $singlePoint)
        // Single point mode
        const statusSelect = document.querySelector('select[name="status"]');
        const measuredValueInput = document.querySelector('input[name="measured_value"]');
        const status = statusSelect ? statusSelect.value : '';
        const measuredValue = measuredValueInput ? measuredValueInput.value : '';
        
        if (status === 'completed' && (!measuredValue || measuredValue.trim() === '')) {
            hasError = true;
            const statusWarning = document.getElementById('status-warning-0');
            if (statusWarning) statusWarning.classList.remove('hidden');
            if (measuredValueInput) measuredValueInput.focus();
        }
    @else
        // Batch mode
        const statusSelects = document.querySelectorAll('.execution-status');
        
        statusSelects.forEach(function(select) {
            const index = select.dataset.index;
            const status = select.value;
            const measuredValueInput = document.querySelector(`input[name="executions[${index}][measured_value]"]`);
            const measuredValue = measuredValueInput ? measuredValueInput.value : '';
            
            if (status === 'completed' && (!measuredValue || measuredValue.trim() === '')) {
                hasError = true;
                const statusWarning = document.getElementById(`status-warning-${index}`);
                if (statusWarning) statusWarning.classList.remove('hidden');
                if (measuredValueInput) measuredValueInput.focus();
            }
        });
    @endif
    
    if (hasError) {
        e.preventDefault();
        alert('Measured value harus diisi untuk semua point dengan status Completed.');
        return false;
    }
});

// Auto set start/end time based on status change for each point
document.querySelectorAll('.execution-status').forEach(function(select) {
    select.addEventListener('change', function() {
        const status = this.value;
        const index = this.dataset.index;
        
        // Auto set times if needed (can be added later if needed)
        if (status === 'completed') {
            checkCompletedStatusForPoint(index);
        }
    });
});

// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.execution-status').forEach(function(select) {
        const index = select.dataset.index;
        checkCompletedStatusForPoint(index);
    });
});
</script>
@endsection
