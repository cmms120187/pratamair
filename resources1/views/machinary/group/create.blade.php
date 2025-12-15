@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="maintenancePointsData()">
    <div class="w-full mx-auto max-w-6xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Machine Type</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('machine-types.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Machine Type Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Machine Type Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <input type="text" name="model" id="model" value="{{ old('model') }}" class="w-full border rounded px-3 py-2 @error('model') border-red-500 @enderror">
                        @error('model')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">Group <span class="text-red-500">*</span></label>
                        <select name="group_id" id="group_id" class="w-full border rounded px-3 py-2 @error('group_id') border-red-500 @enderror" required>
                            <option value="">Select Group</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" data-systems="{{ $group->systems->pluck('id')->toJson() }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Systems will be automatically selected based on the group</p>
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="w-full border rounded px-3 py-2 @error('brand') border-red-500 @enderror">
                        @error('brand')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label for="systems" class="block text-sm font-medium text-gray-700 mb-2">Systems <span class="text-xs text-gray-500">(Auto-selected from Group)</span></label>
                    <select name="systems[]" id="systems" class="w-full border rounded px-3 py-2" multiple size="5" readonly style="background-color: #f3f4f6;">
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}">{{ $system->nama_sistem }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Systems are automatically selected based on the selected Group</p>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Photo (Default untuk Machine Type)</label>
                    <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded px-3 py-2 @error('photo') border-red-500 @enderror" onchange="previewPhoto(event)">
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 5MB). Photo ini akan digunakan sebagai default untuk machine type ini.</p>
                    <div id="photo_preview" class="hidden mt-3">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Photo Preview:</p>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 inline-block">
                            <img id="photo_preview_img" src="" alt="Preview" class="max-w-xs max-h-64 object-contain rounded border shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Points Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Maintenance Points</h2>
                    <button type="button" @click="addPoint()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Point
                    </button>
                </div>

                <!-- Form Tambah Point -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4" x-show="showAddForm">
                    <h3 class="text-md font-semibold text-gray-700 mb-3">Tambah Point Baru</h3>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select x-model="newPoint.category" @change="toggleStandardField()" class="w-full border rounded px-3 py-2">
                                <option value="autonomous">Autonomous Maintenance</option>
                                <option value="preventive">Preventive Maintenance</option>
                                <option value="predictive">Predictive Maintenance</option>
                            </select>
                        </div>
                        <div class="md:col-span-3" x-show="newPoint.category === 'predictive'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Standard <span class="text-red-500">*</span></label>
                            <select x-model="newPoint.standard_id" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih Standard</option>
                                @foreach($standards as $standard)
                                    <option value="{{ $standard->id }}">
                                        {{ $standard->name }} 
                                        @if($standard->min_value || $standard->max_value)
                                            ({{ $standard->min_value ?? '?' }}-{{ $standard->max_value ?? '?' }} {{ $standard->unit ?? '' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Periode Maintenance</label>
                            <select x-model="newPoint.frequency_type" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih Periode</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Periode</label>
                            <input type="number" x-model="newPoint.frequency_value" class="w-full border rounded px-3 py-2" min="1" placeholder="1" value="1">
                            <p class="text-xs text-gray-500 mt-1">e.g., Setiap 2 minggu = 2</p>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Point <span class="text-red-500">*</span></label>
                            <input type="text" x-model="newPoint.name" class="w-full border rounded px-3 py-2" placeholder="Masukkan nama point">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                            <input type="number" x-model="newPoint.sequence" class="w-full border rounded px-3 py-2" min="0" value="0">
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi</label>
                            <textarea x-model="newPoint.instruction" class="w-full border rounded px-3 py-2" rows="2" placeholder="Masukkan instruksi"></textarea>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                            <input type="file" @change="handlePhotoChange($event)" accept="image/*" class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                            <div x-show="newPoint.photoPreview" class="mt-2">
                                <img :src="newPoint.photoPreview" alt="Preview" class="max-w-xs max-h-32 object-cover rounded border">
                            </div>
                        </div>
                        <div class="md:col-span-12 flex gap-2">
                            <button type="button" @click="saveNewPoint()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                                Simpan Point
                            </button>
                            <button type="button" @click="cancelAddPoint()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- List Points by Category -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <template x-for="(categoryKey, categoryLabel) in categories" :key="categoryKey">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg mb-2">
                                <h3 class="text-md font-semibold" x-text="categoryLabel"></h3>
                            </div>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <template x-for="(point, index) in getPointsByCategory(categoryKey)" :key="point.tempId">
                                    <div class="bg-white rounded p-3 border border-gray-200">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900" x-text="point.name"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="point.instruction ? (point.instruction.length > 40 ? point.instruction.substring(0, 40) + '...' : point.instruction) : '-'"></p>
                                                <p class="text-xs text-gray-400 mt-1" x-show="point.frequency_type">
                                                    Periode: <span x-text="point.frequency_type ? (point.frequency_type.charAt(0).toUpperCase() + point.frequency_type.slice(1) + ' (' + (point.frequency_value || 1) + 'x)') : '-'"></span>
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">Urutan: <span x-text="point.sequence"></span></p>
                                                <div x-show="point.photoPreview" class="mt-2">
                                                    <img :src="point.photoPreview" alt="Photo" class="max-w-full max-h-20 object-cover rounded border">
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" @click="editPoint(point)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs">
                                                    Edit
                                                </button>
                                                <button type="button" @click="removePoint(point.tempId)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="getPointsByCategory(categoryKey).length === 0" class="text-center text-sm text-gray-500 py-4">
                                    Belum ada point
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Hidden inputs and file inputs for maintenance points -->
                <template x-for="(point, index) in points" :key="point.tempId">
                    <div>
                        <input type="hidden" :name="'maintenance_points[' + index + '][category]'" :value="point.category">
                        <input type="hidden" :name="'maintenance_points[' + index + '][standard_id]'" :value="point.standard_id || ''">
                        <input type="hidden" :name="'maintenance_points[' + index + '][frequency_type]'" :value="point.frequency_type || ''">
                        <input type="hidden" :name="'maintenance_points[' + index + '][frequency_value]'" :value="point.frequency_value || 1">
                        <input type="hidden" :name="'maintenance_points[' + index + '][name]'" :value="point.name">
                        <input type="hidden" :name="'maintenance_points[' + index + '][instruction]'" :value="point.instruction || ''">
                        <input type="hidden" :name="'maintenance_points[' + index + '][sequence]'" :value="point.sequence">
                        <input type="file" :name="'maintenance_points[' + index + '][photo]'" accept="image/*" :id="'photo_' + point.tempId" class="hidden" @change="updatePointPhoto(point.tempId, $event)">
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Create Machine Type</button>
                <a href="{{ route('machine-types.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto-select systems when group is selected
document.addEventListener('DOMContentLoaded', function() {
    const groupSelect = document.getElementById('group_id');
    const systemsSelect = document.getElementById('systems');
    
    if (groupSelect && systemsSelect) {
        groupSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const systemIds = selectedOption ? JSON.parse(selectedOption.getAttribute('data-systems') || '[]') : [];
            
            // Clear all selections
            Array.from(systemsSelect.options).forEach(option => {
                option.selected = false;
            });
            
            // Select systems from group
            systemIds.forEach(systemId => {
                const option = systemsSelect.querySelector(`option[value="${systemId}"]`);
                if (option) {
                    option.selected = true;
                }
            });
        });
        
        // Trigger on page load if group is already selected
        if (groupSelect.value) {
            groupSelect.dispatchEvent(new Event('change'));
        }
    }
});

function maintenancePointsData() {
    return {
        points: [],
        tempIdCounter: 0,
        showAddForm: false,
        editingPoint: null,
        categories: {
            'autonomous': 'Autonomous Maintenance',
            'preventive': 'Preventive Maintenance',
            'predictive': 'Predictive Maintenance'
        },
        newPoint: {
            category: 'preventive',
            standard_id: '',
            frequency_type: '',
            frequency_value: 1,
            name: '',
            instruction: '',
            sequence: 0,
            photoFile: null,
            photoPreview: null
        },
        toggleStandardField() {
            // Field akan otomatis show/hide dengan x-show
        },
        addPoint() {
            this.showAddForm = true;
            this.editingPoint = null;
            this.newPoint = {
                category: 'preventive',
                standard_id: '',
                frequency_type: '',
                frequency_value: 1,
                name: '',
                instruction: '',
                sequence: 0,
                photoFile: null,
                photoPreview: null
            };
        },
        handlePhotoChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.newPoint.photoFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newPoint.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        saveNewPoint() {
            if (!this.newPoint.name.trim()) {
                alert('Nama point harus diisi');
                return;
            }
            
            if (this.editingPoint) {
                // Update existing point
                const index = this.points.findIndex(p => p.tempId === this.editingPoint.tempId);
                if (index !== -1) {
                    this.points[index] = {
                        ...this.newPoint,
                        tempId: this.editingPoint.tempId,
                        photoFile: this.newPoint.photoFile
                    };
                }
            } else {
                // Add new point
                const newPoint = {
                    ...this.newPoint,
                    tempId: 'temp_' + (this.tempIdCounter++),
                    photoFile: this.newPoint.photoFile
                };
                this.points.push(newPoint);
                
                // Create file input for this point
                setTimeout(() => {
                    const fileInput = document.getElementById('photo_' + newPoint.tempId);
                    if (fileInput && this.newPoint.photoFile) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(this.newPoint.photoFile);
                        fileInput.files = dataTransfer.files;
                    }
                }, 100);
            }
            
            this.cancelAddPoint();
        },
        cancelAddPoint() {
            this.showAddForm = false;
            this.editingPoint = null;
            this.newPoint = {
                category: 'preventive',
                standard_id: '',
                frequency_type: '',
                frequency_value: 1,
                name: '',
                instruction: '',
                sequence: 0,
                photoFile: null,
                photoPreview: null
            };
        },
        updatePointPhoto(tempId, event) {
            const file = event.target.files[0];
            if (file) {
                const point = this.points.find(p => p.tempId === tempId);
                if (point) {
                    point.photoFile = file;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        point.photoPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        },
        editPoint(point) {
            this.editingPoint = point;
            this.newPoint = {
                category: point.category,
                standard_id: point.standard_id || '',
                frequency_type: point.frequency_type || '',
                frequency_value: point.frequency_value || 1,
                name: point.name,
                instruction: point.instruction || '',
                sequence: point.sequence || 0,
                photoFile: point.photoFile || null,
                photoPreview: point.photoPreview || null
            };
            this.showAddForm = true;
        },
        removePoint(tempId) {
            if (confirm('Hapus point ini?')) {
                this.points = this.points.filter(p => p.tempId !== tempId);
            }
        },
        getPointsByCategory(category) {
            return this.points.filter(p => p.category === category);
        }
    }
}

// Photo preview for main form
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo_preview_img').src = e.target.result;
            document.getElementById('photo_preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('photo_preview').classList.add('hidden');
    }
}
</script>
@endpush
@endsection
