@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Buat Template Inspeksi</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inspection-templates.store') }}" method="POST" id="templateForm" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Template</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Machine Type -->
                    <div>
                        <label for="machine_type_id" class="block text-sm font-medium text-gray-700 mb-2">Machine Type <span class="text-red-500">*</span></label>
                        <select name="machine_type_id" id="machine_type_id" class="w-full border rounded px-3 py-2 @error('machine_type_id') border-red-500 @enderror" required>
                            <option value="">Pilih Machine Type</option>
                            @foreach($machineTypes as $type)
                                <option value="{{ $type->id }}" {{ old('machine_type_id', request('machine_type_id')) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('machine_type_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Template Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Template <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" 
                               placeholder="Contoh: Inspeksi Harian Mesin A"
                               required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Parameters Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Parameter Template</h2>
                    <button type="button" onclick="addParameter()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Parameter
                    </button>
                </div>
                
                <div id="parametersContainer">
                    <!-- Parameters will be added here dynamically -->
                </div>
                
                @error('parameters')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inspection-templates.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let parameterIndex = 0;

function addParameter() {
    const container = document.getElementById('parametersContainer');
    const paramDiv = document.createElement('div');
    paramDiv.className = 'border rounded p-4 mb-4 parameter-item';
    paramDiv.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-md font-semibold text-gray-700">Parameter ${parameterIndex + 1}</h3>
            <button type="button" onclick="this.closest('.parameter-item').remove()" class="text-red-600 hover:text-red-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Parameter <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="parameters[${parameterIndex}][parameter_name]" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="Contoh: Suhu"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                <input type="text" 
                       name="parameters[${parameterIndex}][unit]" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="Contoh: °C, bar, psi">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Minimum</label>
                <input type="number" 
                       step="0.0001"
                       name="parameters[${parameterIndex}][min_value]" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="0.0000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Maximum</label>
                <input type="number" 
                       step="0.0001"
                       name="parameters[${parameterIndex}][max_value]" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="0.0000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                <input type="number" 
                       name="parameters[${parameterIndex}][sequence]" 
                       value="${parameterIndex}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Parameter/Referensi</label>
                <input type="file" 
                       name="parameters[${parameterIndex}][photo]" 
                       accept="image/*"
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF (Max 2MB)</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi Pengukuran</label>
                <textarea name="parameters[${parameterIndex}][instruction]" 
                          rows="2"
                          class="w-full border rounded px-3 py-2"
                          placeholder="Cara mengukur parameter ini..."></textarea>
            </div>
        </div>
    `;
    container.appendChild(paramDiv);
    parameterIndex++;
}

// Add first parameter on page load
document.addEventListener('DOMContentLoaded', function() {
    addParameter();
});
</script>
@endsection
