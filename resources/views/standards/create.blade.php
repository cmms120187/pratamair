@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create Standard</h1>
            <a href="{{ route('standards.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

        <form action="{{ route('standards.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <input type="text" name="unit" id="unit" value="{{ old('unit') }}" placeholder="°C, bar, mm/s, etc" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <!-- Reference Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Reference Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="reference_type" class="block text-sm font-medium text-gray-700 mb-2">Reference Type</label>
                            <select name="reference_type" id="reference_type" class="w-full border rounded px-3 py-2">
                                <option value="">Select Type</option>
                                <option value="ISO" {{ old('reference_type') == 'ISO' ? 'selected' : '' }}>ISO</option>
                                <option value="Factory Standard" {{ old('reference_type') == 'Factory Standard' ? 'selected' : '' }}>Factory Standard</option>
                                <option value="Custom" {{ old('reference_type') == 'Custom' ? 'selected' : '' }}>Custom</option>
                                <option value="Industry Standard" {{ old('reference_type') == 'Industry Standard' ? 'selected' : '' }}>Industry Standard</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="reference_code" class="block text-sm font-medium text-gray-700 mb-2">Reference Code</label>
                            <input type="text" name="reference_code" id="reference_code" value="{{ old('reference_code') }}" placeholder="ISO 9001, FS-001, etc" class="w-full border rounded px-3 py-2">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="reference_name" class="block text-sm font-medium text-gray-700 mb-2">Reference Name</label>
                            <input type="text" name="reference_name" id="reference_name" value="{{ old('reference_name') }}" placeholder="ISO 9001:2015 Quality Management" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <!-- Standard Values -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Standard Values</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="min_value" class="block text-sm font-medium text-gray-700 mb-2">Min Value</label>
                            <input type="number" step="0.0001" name="min_value" id="min_value" value="{{ old('min_value') }}" class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Akan diisi otomatis dari variants jika ada</p>
                        </div>
                        
                        <div>
                            <label for="max_value" class="block text-sm font-medium text-gray-700 mb-2">Max Value</label>
                            <input type="number" step="0.0001" name="max_value" id="max_value" value="{{ old('max_value') }}" class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Akan diisi otomatis dari variants jika ada</p>
                        </div>
                        
                        <div>
                            <label for="target_value" class="block text-sm font-medium text-gray-700 mb-2">Target Value</label>
                            <input type="number" step="0.0001" name="target_value" id="target_value" value="{{ old('target_value') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <!-- Standard Variants -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Standard Variants (Zones/Levels)</h3>
                        <button type="button" onclick="addVariantRow()" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-1 px-3 rounded shadow transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Variant
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Tambahkan variants untuk standar yang memiliki beberapa zone/level (contoh: ISO 10816-3 dengan zone A, B, C, D)</p>
                    <div id="variants-container" class="space-y-3">
                        <!-- Variant rows will be added here dynamically -->
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                        <div class="border rounded px-3 py-2 max-h-48 overflow-y-auto">
                            <div class="space-y-2">
                                @foreach($machineTypes as $machineType)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="machine_type_ids[]" value="{{ $machineType->id }}" 
                                               {{ in_array($machineType->id, old('machine_type_ids', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $machineType->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Pilih satu atau lebih machine type. Kosongkan jika untuk semua machine type.</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="mt-4">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" placeholder="sample" class="w-full border rounded px-3 py-2">{{ old('keterangan', 'sample') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Kolom keterangan dengan contoh teks "sample"</p>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                        <div class="space-y-4">
                            <!-- Pilih Photo yang Sudah Ada -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Photo yang Sudah Ada (Opsional)</label>
                                <div class="border rounded px-3 py-2 max-h-48 overflow-y-auto">
                                    @if($standardPhotos->count() > 0)
                                        <div class="grid grid-cols-2 gap-3">
                                            @foreach($standardPhotos as $photo)
                                                <label class="flex items-start p-2 border rounded hover:bg-gray-50 cursor-pointer">
                                                    <input type="checkbox" name="standard_photo_ids[]" value="{{ $photo->id }}" 
                                                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <div class="ml-2 flex-1">
                                                        <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->name }}" class="w-full h-20 object-cover rounded mb-1">
                                                        <p class="text-xs text-gray-600">{{ $photo->name ?? 'Photo #' . $photo->id }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">Belum ada photo yang tersedia. Upload photo baru di bawah.</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Upload Photo Baru -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Atau Upload Photo Baru (Opsional)</label>
                                <div class="space-y-2">
                                    <input type="text" name="photo_name" id="photo_name" placeholder="Nama photo (opsional)" class="w-full border rounded px-3 py-2">
                                    <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded px-3 py-2">
                                    <p class="text-xs text-gray-500">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                                    <div id="photo-preview" class="mt-2 hidden">
                                        <img id="photo-preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded border">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('standards.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Create Standard
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let variantIndex = 0;
    const colorOptions = [
        { value: '#22C55E', label: 'Hijau (Good)' },
        { value: '#FACC15', label: 'Kuning (Warning)' },
        { value: '#FB923C', label: 'Orange (Caution)' },
        { value: '#EF4444', label: 'Merah (Danger)' },
        { value: '#3B82F6', label: 'Biru' },
        { value: '#8B5CF6', label: 'Ungu' },
    ];

    function addVariantRow(variant = null) {
        const container = document.getElementById('variants-container');
        const row = document.createElement('div');
        row.className = 'border rounded p-4 bg-gray-50 variant-row';
        row.dataset.index = variantIndex;
        
        const isEdit = variant !== null;
        const variantData = variant || { name: '', min_value: '', max_value: '', color: '#22C55E', order: variantIndex + 1 };
        
        row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Variant Name</label>
                    <input type="text" name="variants[${variantIndex}][name]" value="${variantData.name || ''}" 
                           placeholder="e.g. New machine condition" 
                           class="w-full border rounded px-2 py-1 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Min Value</label>
                    <input type="number" step="0.0001" name="variants[${variantIndex}][min_value]" 
                           value="${variantData.min_value || ''}" 
                           class="w-full border rounded px-2 py-1 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Max Value</label>
                    <input type="number" step="0.0001" name="variants[${variantIndex}][max_value]" 
                           value="${variantData.max_value || ''}" 
                           class="w-full border rounded px-2 py-1 text-sm" required>
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
                        <input type="color" name="variants[${variantIndex}][color]" 
                               value="${variantData.color || '#22C55E'}" 
                               class="w-full h-8 border rounded" title="Pilih warna">
                    </div>
                    <button type="button" onclick="removeVariantRow(this)" 
                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm" 
                            title="Hapus variant">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <input type="hidden" name="variants[${variantIndex}][order]" value="${variantData.order || variantIndex + 1}">
        `;
        
        container.appendChild(row);
        variantIndex++;
    }

    function removeVariantRow(button) {
        const row = button.closest('.variant-row');
        row.remove();
    }

    // Photo preview
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview-img').src = e.target.result;
                document.getElementById('photo-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('photo-preview').classList.add('hidden');
        }
    });
</script>
@endsection

