@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Buat Inspeksi Harian</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inspections.updating.store') }}" method="POST" id="inspectionForm">
            @if(isset($schedule) && $schedule)
                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
            @endif
            @csrf
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Inspeksi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Machine -->
                    <div>
                        <label for="machine_erp_id" class="block text-sm font-medium text-gray-700 mb-2">Mesin <span class="text-red-500">*</span></label>
                        <select name="machine_erp_id" id="machine_erp_id" class="w-full border rounded px-3 py-2 @error('machine_erp_id') border-red-500 @enderror" required onchange="loadTemplate()">
                            <option value="">Pilih Mesin</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" 
                                        data-machine-type-id="{{ $machine->machine_type_id }}"
                                        {{ old('machine_erp_id', $machineId) == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->idMachine }} - {{ $machine->machineType->name ?? $machine->type_name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        @error('machine_erp_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Inspection Date -->
                    <div>
                        <label for="inspection_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                        <input type="date" 
                               name="inspection_date" 
                               id="inspection_date" 
                               value="{{ old('inspection_date', $inspectionDate) }}" 
                               class="w-full border rounded px-3 py-2 @error('inspection_date') border-red-500 @enderror" 
                               required>
                        @error('inspection_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Performed By -->
                    <div>
                        <label for="performed_by" class="block text-sm font-medium text-gray-700 mb-2">Dilakukan Oleh</label>
                        <select name="performed_by" id="performed_by" class="w-full border rounded px-3 py-2 @error('performed_by') border-red-500 @enderror">
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('performed_by', auth()->id()) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('performed_by')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                </div>
            </div>

            <!-- Template Info -->
            <div id="templateInfo" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6" style="display: none;">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-md font-semibold text-blue-800">Template Inspeksi</h3>
                </div>
                <p class="text-sm text-blue-700" id="templateName"></p>
                <p class="text-xs text-blue-600 mt-1" id="templateDescription"></p>
            </div>

            <!-- Parameters Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Parameter Inspeksi</h2>
                </div>
                
                <div id="parametersContainer">
                    <div class="text-center py-8 text-gray-500">
                        <p>Pilih mesin terlebih dahulu untuk memuat template inspeksi</p>
                    </div>
                </div>
                
                <input type="hidden" name="template_id" id="template_id" value="{{ old('template_id', $template->id ?? '') }}" required>
                
                @error('template_id')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
                @error('parameters')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan dan Informasi Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Catatan dan Informasi</h2>
                <div class="space-y-4">
                    <!-- Catatan Inspeksi -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan Inspeksi</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror" placeholder="Catatan umum tentang inspeksi ini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Informasi Status -->
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">Informasi Status</h3>
                        <p class="text-xs text-blue-700 mb-2">
                            Status akan dihitung otomatis berdasarkan nilai pengukuran dan range yang telah ditentukan:
                        </p>
                        <div class="space-y-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                <span class="text-gray-700"><strong>Normal:</strong> Nilai dalam range yang ditentukan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                <span class="text-gray-700"><strong>Warning:</strong> Nilai mendekati batas (10% margin dari range)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="text-gray-700"><strong>Critical:</strong> Nilai di luar range yang ditentukan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inspections.updating.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan Inspeksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentTemplate = null;

// Load template when machine is selected
function loadTemplate() {
    const machineSelect = document.getElementById('machine_erp_id');
    const selectedOption = machineSelect.options[machineSelect.selectedIndex];
    const machineTypeId = selectedOption.getAttribute('data-machine-type-id');
    
    if (!machineTypeId) {
        document.getElementById('parametersContainer').innerHTML = '<div class="text-center py-8 text-gray-500"><p>Pilih mesin terlebih dahulu untuk memuat template inspeksi</p></div>';
        document.getElementById('templateInfo').style.display = 'none';
        document.getElementById('template_id').value = '';
        return;
    }
    
    // Show loading
    document.getElementById('parametersContainer').innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-500">Memuat template...</p></div>';
    
    // Fetch template
    fetch(`{{ route('inspections.get-template-by-machine-type') }}?machine_type_id=${machineTypeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.template) {
                currentTemplate = data.template;
                document.getElementById('template_id').value = data.template.id;
                document.getElementById('templateName').textContent = data.template.name;
                document.getElementById('templateDescription').textContent = data.template.description || '';
                document.getElementById('templateInfo').style.display = 'block';
                renderParameters(data.template.parameters);
            } else {
                document.getElementById('parametersContainer').innerHTML = '<div class="text-center py-8 text-yellow-600"><p>⚠️ Tidak ada template inspeksi untuk machine type ini. Silakan buat template terlebih dahulu.</p><a href="{{ route("inspection-templates.create") }}?machine_type_id=' + machineTypeId + '" class="text-blue-600 hover:underline mt-2 inline-block">Buat Template</a></div>';
                document.getElementById('templateInfo').style.display = 'none';
                document.getElementById('template_id').value = '';
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
            document.getElementById('parametersContainer').innerHTML = '<div class="text-center py-8 text-red-500"><p>Error memuat template. Silakan coba lagi.</p></div>';
        });
}

function renderParameters(parameters) {
    const container = document.getElementById('parametersContainer');
    container.innerHTML = '';
    
    if (!parameters || parameters.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500"><p>Tidak ada parameter dalam template ini.</p></div>';
        return;
    }
    
    // Sort by sequence
    const sortedParams = [...parameters].sort((a, b) => (a.sequence || 0) - (b.sequence || 0));
    
    sortedParams.forEach((param, index) => {
        const paramDiv = document.createElement('div');
        paramDiv.className = 'border rounded p-4 mb-4 parameter-item';
        
        const rangeText = (param.min_value !== null || param.max_value !== null) 
            ? `${param.min_value !== null ? param.min_value : '-'} - ${param.max_value !== null ? param.max_value : '-'} ${param.unit || ''}`
            : 'Tidak ada range';
        
        paramDiv.innerHTML = `
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-1">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">${param.parameter_name}</h3>
                    <p class="text-xs text-gray-500 mb-1">Range: ${rangeText}</p>
                    ${param.instruction ? `<p class="text-xs text-gray-600 mb-3">📋 ${param.instruction}</p>` : ''}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Pengukuran <span class="text-red-500">*</span></label>
                        <input type="number" 
                               step="0.0001"
                               name="parameters[${index}][parameter_value]" 
                               class="w-full border rounded px-3 py-2" 
                               placeholder="0.0000"
                               required>
                    </div>
                </div>
                ${param.photo ? `
                <div class="flex-shrink-0">
                    <p class="text-xs text-gray-500 mb-2 text-center">Foto Referensi</p>
                    <img src="${param.photo}" alt="${param.parameter_name}" class="w-32 h-32 object-cover rounded border cursor-pointer hover:opacity-80 transition" onclick="window.open('${param.photo}', '_blank')" title="Klik untuk memperbesar">
                </div>
                ` : ''}
            </div>
            <input type="hidden" name="parameters[${index}][template_parameter_id]" value="${param.id}">
            <input type="hidden" name="parameters[${index}][notes]" value="">
        `;
        
        container.appendChild(paramDiv);
    });
}

// Load template on page load if machine is already selected
document.addEventListener('DOMContentLoaded', function() {
    const machineSelect = document.getElementById('machine_erp_id');
    if (machineSelect.value) {
        loadTemplate();
    }
    
    // Load template if already provided from server
    @if(isset($template) && $template)
        currentTemplate = @json($template);
        document.getElementById('template_id').value = {{ $template->id }};
        document.getElementById('templateName').textContent = '{{ $template->name }}';
        document.getElementById('templateDescription').textContent = '{{ $template->description ?? '' }}';
        document.getElementById('templateInfo').style.display = 'block';
        renderParameters(@json($template->parameters));
    @endif
});
</script>
@endsection
