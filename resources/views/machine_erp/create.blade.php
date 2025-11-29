@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Machine ERP</h1>
            <p class="text-sm text-gray-600">Add new machine ERP entry (all fields are manual input)</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('machine-erp.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="idMachine" class="block text-sm font-semibold text-gray-700 mb-2">ID Machine <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="idMachine" 
                       id="idMachine" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idMachine') border-red-500 @enderror" 
                       value="{{ old('idMachine') }}" 
                       required
                       placeholder="Enter machine ID">
                @error('idMachine')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <label for="room_erp_select" class="block text-sm font-semibold text-gray-700 mb-2">
                    Pilih Room ERP (untuk auto-fill Plant/Process/Line/Room)
                </label>
                <select id="room_erp_select" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">-- Pilih Room ERP atau isi manual --</option>
                    @foreach($roomErps as $roomErp)
                        <option value="{{ $roomErp->id }}" 
                                data-plant="{{ $roomErp->plant_name ?? '' }}"
                                data-process="{{ $roomErp->process_name ?? '' }}"
                                data-line="{{ $roomErp->line_name ?? '' }}"
                                data-room="{{ $roomErp->name ?? '' }}"
                                data-kode-room="{{ $roomErp->kode_room ?? '' }}">
                            {{ $roomErp->kode_room ? $roomErp->kode_room . ' - ' : '' }}{{ $roomErp->name }}
                            @if($roomErp->plant_name)
                                ({{ $roomErp->plant_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih room untuk mengisi otomatis field Plant, Process, Line, dan Room Name</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="plant_name" class="block text-sm font-semibold text-gray-700 mb-2">Plant Name</label>
                    <input type="text" 
                           name="plant_name" 
                           id="plant_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant_name') border-red-500 @enderror" 
                           value="{{ old('plant_name') }}" 
                           placeholder="Enter plant name">
                    @error('plant_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process Name</label>
                    <input type="text" 
                           name="process_name" 
                           id="process_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_name') border-red-500 @enderror" 
                           value="{{ old('process_name') }}" 
                           placeholder="Enter process name">
                    @error('process_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="line_name" class="block text-sm font-semibold text-gray-700 mb-2">Line Name</label>
                    <input type="text" 
                           name="line_name" 
                           id="line_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_name') border-red-500 @enderror" 
                           value="{{ old('line_name') }}" 
                           placeholder="Enter line name">
                    @error('line_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="room_name" class="block text-sm font-semibold text-gray-700 mb-2">Room Name</label>
                <input type="text" 
                       name="room_name" 
                       id="room_name" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('room_name') border-red-500 @enderror" 
                       value="{{ old('room_name') }}" 
                       placeholder="Enter room name">
                @error('room_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hidden field for kode_room -->
            <input type="hidden" name="kode_room" id="kode_room" value="{{ old('kode_room') }}">

                <div class="mb-4 p-4 bg-green-50 rounded-lg border border-green-200">
                    <label for="model_select" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Model (untuk auto-fill Group/System/Type/Brand/Model)
                    </label>
                    <select id="model_select" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">-- Pilih Model atau isi manual --</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">
                                {{ $model->name }}
                                @if($model->machineType)
                                    - {{ $model->machineType->name }}
                                @endif
                                @if($model->brand)
                                    ({{ $model->brand->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih model untuk mengisi otomatis field Group, System, Type, Brand, dan Model</p>
                </div>

                <div>
                    <label for="group_id" class="block text-sm font-semibold text-gray-700 mb-2">Group</label>
                    <input type="hidden" name="group_id" id="group_id" value="{{ old('group_id') }}">
                    <input type="text" 
                           id="group_display" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed" 
                           readonly
                           placeholder="Akan terisi otomatis dari Machine Type">
                    @error('group_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type_name" class="block text-sm font-semibold text-gray-700 mb-2">Type Name</label>
                    <input type="text" 
                           name="type_name" 
                           id="type_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ old('type_name') }}" 
                           readonly
                           placeholder="Akan terisi otomatis dari Machine Type">
                    @error('type_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand_name" class="block text-sm font-semibold text-gray-700 mb-2">Brand Name</label>
                    <input type="text" 
                           name="brand_name" 
                           id="brand_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ old('brand_name') }}" 
                           readonly
                           placeholder="Akan terisi otomatis dari Machine Type">
                    @error('brand_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Display systems information - Full Width -->
            <div id="systems_info" class="hidden mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-sm font-semibold text-gray-900 mb-3">Sistem yang Digunakan:</div>
                <div id="systems_list" class="text-sm text-gray-700"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">Model Name</label>
                    <input type="text" 
                           name="model_name" 
                           id="model_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ old('model_name') }}" 
                           readonly
                           placeholder="Akan terisi otomatis dari Model yang dipilih">
                    @error('model_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="serial_number" class="block text-sm font-semibold text-gray-700 mb-2">Serial Number</label>
                    <input type="text" 
                           name="serial_number" 
                           id="serial_number" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('serial_number') border-red-500 @enderror" 
                           value="{{ old('serial_number') }}" 
                           placeholder="Enter serial number">
                    @error('serial_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tahun_production" class="block text-sm font-semibold text-gray-700 mb-2">Tahun Production</label>
                    <input type="number" 
                           name="tahun_production" 
                           id="tahun_production" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('tahun_production') border-red-500 @enderror" 
                           value="{{ old('tahun_production') }}" 
                           placeholder="Enter tahun production">
                    @error('tahun_production')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="no_document" class="block text-sm font-semibold text-gray-700 mb-2">No Document</label>
                    <input type="text" 
                           name="no_document" 
                           id="no_document" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('no_document') border-red-500 @enderror" 
                           value="{{ old('no_document') }}" 
                           placeholder="Enter document number">
                    @error('no_document')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">Photo (Khusus untuk Model ini)</label>
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('photo') border-red-500 @enderror">
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 5MB). Photo ini spesifik untuk model mesin ini. Jika tidak diisi, akan menggunakan photo default dari Machine Type.</p>
                    <div id="photo_preview" class="hidden mt-2">
                        <img id="photo_preview_img" src="" alt="Preview" class="max-w-xs max-h-48 object-cover rounded border">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Machine ERP
                </button>
                <a href="{{ route('machine-erp.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Groups data from server (already mapped in controller)
const groupsData = @json($groupsData);
// Models data from server
const modelsData = @json($modelsData);

// DOM elements
const roomErpSelect = document.getElementById('room_erp_select');
const modelSelect = document.getElementById('model_select');
const groupIdInput = document.getElementById('group_id');
const groupDisplayInput = document.getElementById('group_display');
const typeNameInput = document.getElementById('type_name');
const brandNameInput = document.getElementById('brand_name');
const modelNameSelect = document.getElementById('model_name');
const systemsInfo = document.getElementById('systems_info');
const systemsList = document.getElementById('systems_list');

// Handle Room ERP selection change
if (roomErpSelect) {
    roomErpSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            document.getElementById('plant_name').value = selectedOption.dataset.plant || '';
            document.getElementById('process_name').value = selectedOption.dataset.process || '';
            document.getElementById('line_name').value = selectedOption.dataset.line || '';
            document.getElementById('room_name').value = selectedOption.dataset.room || '';
            document.getElementById('kode_room').value = selectedOption.dataset.kodeRoom || '';
        } else {
            // Clear fields if no selection
            document.getElementById('plant_name').value = '';
            document.getElementById('process_name').value = '';
            document.getElementById('line_name').value = '';
            document.getElementById('room_name').value = '';
            document.getElementById('kode_room').value = '';
        }
    });
}

// Handle Machine Type selection change
if (machineTypeSelect) {
    machineTypeSelect.addEventListener('change', function() {
        const selectedMachineTypeId = this.value;
        
        if (!selectedMachineTypeId) {
            // Clear all fields
            groupIdInput.value = '';
            groupDisplayInput.value = '';
            typeNameInput.value = '';
            brandNameInput.value = '';
            modelNameSelect.innerHTML = '<option value="">-- Pilih Model --</option>';
            systemsInfo.classList.add('hidden');
            return;
        }
        
        // Find selected machine type
        const selectedMT = machineTypesData.find(mt => mt.id === selectedMachineTypeId);
        
        if (selectedMT) {
            // Fill Type Name
            typeNameInput.value = selectedMT.name || '';
            
            // Fill Brand Name
            brandNameInput.value = selectedMT.brand || '';
            
            // Fill Group
            if (selectedMT.group_id) {
                groupIdInput.value = selectedMT.group_id;
                groupDisplayInput.value = selectedMT.group_name || '';
                
                // Display systems from group
                const selectedGroup = groupsData.find(g => g.id === selectedMT.group_id);
                if (selectedGroup && selectedGroup.systems && selectedGroup.systems.length > 0) {
                    systemsList.innerHTML = selectedGroup.systems.map(system => {
                        return `<div class="mb-1">
                            <span class="font-semibold">${system.nama_sistem || ''}</span>
                            ${system.deskripsi ? `<span class="text-gray-600"> - ${system.deskripsi}</span>` : ''}
                        </div>`;
                    }).join('');
                    systemsInfo.classList.remove('hidden');
                } else {
                    systemsInfo.classList.add('hidden');
                }
            } else {
                groupIdInput.value = '';
                groupDisplayInput.value = '';
                systemsInfo.classList.add('hidden');
            }
            
            // Fill Model dropdown
            modelNameSelect.innerHTML = '<option value="">-- Pilih Model --</option>';
            if (selectedMT.models && selectedMT.models.length > 0) {
                selectedMT.models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.name;
                    option.textContent = model.name;
                    modelNameSelect.appendChild(option);
                });
            } else if (selectedMT.model) {
                // If machine type has a default model, add it
                const option = document.createElement('option');
                option.value = selectedMT.model;
                option.textContent = selectedMT.model;
                modelNameSelect.appendChild(option);
            }
        }
    });
}

// Photo preview for machine ERP
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo_preview');
const photoPreviewImg = document.getElementById('photo_preview_img');

if (photoInput && photoPreview && photoPreviewImg) {
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreviewImg.src = e.target.result;
                photoPreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.classList.add('hidden');
        }
    });
}
</script>
@endsection

