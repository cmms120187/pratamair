@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Machine</h1>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('machines.store') }}" method="POST" id="machineForm">
                @csrf
                
                <div class="mb-4">
                    <label for="idMachine" class="block text-sm font-medium text-gray-700 mb-2">ID Mesin <span class="text-red-500">*</span></label>
                    <input type="text" name="idMachine" id="idMachine" value="{{ old('idMachine') }}" class="w-full border rounded px-3 py-2 @error('idMachine') border-red-500 @enderror" required>
                    @error('idMachine')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-2">Type Machine <span class="text-red-500">*</span></label>
                        <select name="type_id" id="type_id" class="w-full border rounded px-3 py-2 @error('type_id') border-red-500 @enderror" required>
                            <option value="">Select Type Machine</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ old('type_id') == $machineType->id ? 'selected' : '' }}>{{ $machineType->name }}</option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-2">Brand Machine <span class="text-red-500">*</span></label>
                        <select name="brand_id" id="brand_id" class="w-full border rounded px-3 py-2 @error('brand_id') border-red-500 @enderror bg-gray-100" required disabled>
                            <option value="">Pilih Type Machine terlebih dahulu</option>
                        </select>
                        @error('brand_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Pilih Type Machine terlebih dahulu untuk menampilkan Brand</p>
                    </div>

                    <div>
                        <label for="model_id" class="block text-sm font-medium text-gray-700 mb-2">Model Machine <span class="text-red-500">*</span></label>
                        <select name="model_id" id="model_id" class="w-full border rounded px-3 py-2 @error('model_id') border-red-500 @enderror bg-gray-100" required disabled>
                            <option value="">Pilih Type dan Brand terlebih dahulu</option>
                        </select>
                        @error('model_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Pilih Type dan Brand terlebih dahulu untuk menampilkan Model</p>
                    </div>

                    <div>
                        <label for="plant_id" class="block text-sm font-medium text-gray-700 mb-2">Plant <span class="text-red-500">*</span></label>
                        <select name="plant_id" id="plant_id" class="w-full border rounded px-3 py-2 @error('plant_id') border-red-500 @enderror" required>
                            <option value="">Select Plant</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ old('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                            @endforeach
                        </select>
                        @error('plant_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="process_id" class="block text-sm font-medium text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                        <select name="process_id" id="process_id" class="w-full border rounded px-3 py-2 @error('process_id') border-red-500 @enderror" required>
                            <option value="">Select Process</option>
                            @foreach($processes as $process)
                                <option value="{{ $process->id }}" {{ old('process_id') == $process->id ? 'selected' : '' }}>{{ $process->name }}</option>
                            @endforeach
                        </select>
                        @error('process_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                        <select name="line_id" id="line_id" class="w-full border rounded px-3 py-2 @error('line_id') border-red-500 @enderror bg-gray-100" required disabled>
                            <option value="">Pilih Plant terlebih dahulu</option>
                        </select>
                        @error('line_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Pilih Plant terlebih dahulu untuk menampilkan Line</p>
                    </div>

                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Room <span class="text-red-500">*</span></label>
                        <select name="room_id" id="room_id" class="w-full border rounded px-3 py-2 @error('room_id') border-red-500 @enderror bg-gray-100" required disabled>
                            <option value="">Pilih Plant dan Line terlebih dahulu</option>
                        </select>
                        @error('room_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Pilih Plant dan Line terlebih dahulu untuk menampilkan Room</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Create</button>
                    <a href="{{ route('machines.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type_id');
    const brandSelect = document.getElementById('brand_id');
    const modelSelect = document.getElementById('model_id');
    const plantSelect = document.getElementById('plant_id');
    const lineSelect = document.getElementById('line_id');
    const roomSelect = document.getElementById('room_id');

    // Load brands when type changes
    typeSelect.addEventListener('change', function() {
        const typeId = this.value;
        
        // Reset brand and model dropdowns
        brandSelect.innerHTML = '<option value="">Loading...</option>';
        brandSelect.disabled = true;
        brandSelect.classList.add('bg-gray-100');
        brandSelect.classList.remove('bg-white');
        
        modelSelect.innerHTML = '<option value="">Pilih Type dan Brand terlebih dahulu</option>';
        modelSelect.disabled = true;
        modelSelect.classList.add('bg-gray-100');
        modelSelect.classList.remove('bg-white');
        
        if (typeId) {
            const url = `{{ route('machines.get-brands-by-type') }}?type_id=${typeId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
                .then(response => response.text().then(text => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    if (!text || text.trim() === '') {
                        return [];
                    }
                    try {
                        return JSON.parse(text.trim());
                    } catch (e) {
                        throw new Error('Response is not valid JSON');
                    }
                }))
                .then(data => {
                    brandSelect.innerHTML = '<option value="">Select Brand</option>';
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    
                    if (data.length > 0) {
                        data.forEach(brand => {
                            const option = document.createElement('option');
                            option.value = brand.id;
                            option.textContent = brand.name;
                            if ('{{ old('brand_id') }}' == brand.id) {
                                option.selected = true;
                            }
                            brandSelect.appendChild(option);
                        });
                        brandSelect.disabled = false;
                        brandSelect.classList.remove('bg-gray-100');
                        brandSelect.classList.add('bg-white');
                    } else {
                        brandSelect.innerHTML = '<option value="">Tidak ada Brand untuk Type ini</option>';
                        brandSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching brands:', error);
                    brandSelect.innerHTML = '<option value="">Error loading brands</option>';
                    brandSelect.disabled = true;
                });
        } else {
            brandSelect.innerHTML = '<option value="">Pilih Type Machine terlebih dahulu</option>';
            brandSelect.disabled = true;
        }
    });

    // Load models when type or brand changes
    function loadModels() {
        const typeId = typeSelect.value;
        const brandId = brandSelect.value;
        
        modelSelect.innerHTML = '<option value="">Loading...</option>';
        modelSelect.disabled = true;
        modelSelect.classList.add('bg-gray-100');
        modelSelect.classList.remove('bg-white');
        
        if (typeId && brandId) {
            const url = `{{ route('machines.get-models-by-type-and-brand') }}?type_id=${typeId}&brand_id=${brandId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
                .then(response => response.text().then(text => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    if (!text || text.trim() === '') {
                        return [];
                    }
                    try {
                        return JSON.parse(text.trim());
                    } catch (e) {
                        throw new Error('Response is not valid JSON');
                    }
                }))
                .then(data => {
                    modelSelect.innerHTML = '<option value="">Select Model</option>';
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    
                    if (data.length > 0) {
                        data.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model.id;
                            option.textContent = model.name;
                            option.dataset.typeId = model.type_id;
                            option.dataset.brandId = model.brand_id;
                            if ('{{ old('model_id') }}' == model.id) {
                                option.selected = true;
                            }
                            modelSelect.appendChild(option);
                        });
                        modelSelect.disabled = false;
                        modelSelect.classList.remove('bg-gray-100');
                        modelSelect.classList.add('bg-white');
                    } else {
                        modelSelect.innerHTML = '<option value="">Tidak ada Model untuk Type dan Brand ini</option>';
                        modelSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching models:', error);
                    modelSelect.innerHTML = '<option value="">Error loading models</option>';
                    modelSelect.disabled = true;
                });
        } else {
            modelSelect.innerHTML = '<option value="">Pilih Type dan Brand terlebih dahulu</option>';
            modelSelect.disabled = true;
        }
    }

    brandSelect.addEventListener('change', loadModels);

    // Auto-update type_id and brand_id when model is selected
    modelSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.typeId && selectedOption.dataset.brandId) {
            typeSelect.value = selectedOption.dataset.typeId;
            brandSelect.value = selectedOption.dataset.brandId;
        }
    });

    // Load lines when plant changes
    plantSelect.addEventListener('change', function() {
        const plantId = this.value;
        
        // Reset line and room dropdowns
        lineSelect.innerHTML = '<option value="">Loading...</option>';
        lineSelect.disabled = true;
        lineSelect.classList.add('bg-gray-100');
        lineSelect.classList.remove('bg-white');
        
        roomSelect.innerHTML = '<option value="">Pilih Plant dan Line terlebih dahulu</option>';
        roomSelect.disabled = true;
        roomSelect.classList.add('bg-gray-100');
        roomSelect.classList.remove('bg-white');
        
        if (plantId) {
            const url = `{{ route('machines.get-lines-by-plant') }}?plant_id=${plantId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
                .then(response => response.text().then(text => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    if (!text || text.trim() === '') {
                        return [];
                    }
                    try {
                        return JSON.parse(text.trim());
                    } catch (e) {
                        throw new Error('Response is not valid JSON');
                    }
                }))
                .then(data => {
                    lineSelect.innerHTML = '<option value="">Select Line</option>';
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    
                    if (data.length > 0) {
                        data.forEach(line => {
                            const option = document.createElement('option');
                            option.value = line.id;
                            option.textContent = line.name;
                            if ('{{ old('line_id') }}' == line.id) {
                                option.selected = true;
                            }
                            lineSelect.appendChild(option);
                        });
                        lineSelect.disabled = false;
                        lineSelect.classList.remove('bg-gray-100');
                        lineSelect.classList.add('bg-white');
                    } else {
                        lineSelect.innerHTML = '<option value="">Tidak ada Line untuk Plant ini</option>';
                        lineSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching lines:', error);
                    lineSelect.innerHTML = '<option value="">Error loading lines</option>';
                    lineSelect.disabled = true;
                });
        } else {
            lineSelect.innerHTML = '<option value="">Pilih Plant terlebih dahulu</option>';
            lineSelect.disabled = true;
        }
    });

    // Load rooms when plant or line changes
    function loadRooms() {
        const plantId = plantSelect.value;
        const lineId = lineSelect.value;
        
        roomSelect.innerHTML = '<option value="">Loading...</option>';
        roomSelect.disabled = true;
        roomSelect.classList.add('bg-gray-100');
        roomSelect.classList.remove('bg-white');
        
        if (plantId && lineId) {
            const url = `{{ route('machines.get-rooms-by-plant-and-line') }}?plant_id=${plantId}&line_id=${lineId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
                .then(response => response.text().then(text => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    if (!text || text.trim() === '') {
                        return [];
                    }
                    try {
                        return JSON.parse(text.trim());
                    } catch (e) {
                        throw new Error('Response is not valid JSON');
                    }
                }))
                .then(data => {
                    roomSelect.innerHTML = '<option value="">Select Room</option>';
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    
                    if (data.length > 0) {
                        data.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id;
                            option.textContent = room.name;
                            if ('{{ old('room_id') }}' == room.id) {
                                option.selected = true;
                            }
                            roomSelect.appendChild(option);
                        });
                        roomSelect.disabled = false;
                        roomSelect.classList.remove('bg-gray-100');
                        roomSelect.classList.add('bg-white');
                    } else {
                        roomSelect.innerHTML = '<option value="">Tidak ada Room untuk Plant dan Line ini</option>';
                        roomSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching rooms:', error);
                    roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
                    roomSelect.disabled = true;
                });
        } else {
            roomSelect.innerHTML = '<option value="">Pilih Plant dan Line terlebih dahulu</option>';
            roomSelect.disabled = true;
        }
    }

    lineSelect.addEventListener('change', loadRooms);

    // Load initial data if old input exists
    @if(old('type_id'))
        typeSelect.value = '{{ old('type_id') }}';
        typeSelect.dispatchEvent(new Event('change'));
    @endif

    @if(old('plant_id'))
        plantSelect.value = '{{ old('plant_id') }}';
        plantSelect.dispatchEvent(new Event('change'));
    @endif

    // Enable disabled selects before form submission
    document.getElementById('machineForm').addEventListener('submit', function(e) {
        if (brandSelect.disabled && brandSelect.value) {
            brandSelect.disabled = false;
        }
        if (modelSelect.disabled && modelSelect.value) {
            modelSelect.disabled = false;
        }
        if (lineSelect.disabled && lineSelect.value) {
            lineSelect.disabled = false;
        }
        if (roomSelect.disabled && roomSelect.value) {
            roomSelect.disabled = false;
        }
    });
});
</script>
@endpush
@endsection
