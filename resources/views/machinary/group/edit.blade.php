@extends('layouts.app')
@section('content')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="maintenancePointsData()">
    <div class="w-full mx-auto max-w-6xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Machine Type</h1>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('machine-types.update', $machineType->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Machine Type Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Machine Type Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $machineType->name) }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <input type="text" name="model" id="model" value="{{ old('model', $machineType->model) }}" class="w-full border rounded px-3 py-2 @error('model') border-red-500 @enderror">
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
                                <option value="{{ $group->id }}" data-systems="{{ $group->systems->pluck('id')->toJson() }}" {{ old('group_id', $machineType->group_id) == $group->id ? 'selected' : '' }}>
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
                        <input type="text" name="brand" id="brand" value="{{ old('brand', $machineType->brand) }}" class="w-full border rounded px-3 py-2 @error('brand') border-red-500 @enderror">
                        @error('brand')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label for="systems" class="block text-sm font-medium text-gray-700 mb-2">Systems <span class="text-xs text-gray-500">(Auto-selected from Group)</span></label>
                    <select name="systems[]" id="systems" class="w-full border rounded px-3 py-2" multiple size="5" readonly style="background-color: #f3f4f6;">
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}" {{ $machineType->systems->contains($system->id) ? 'selected' : '' }}>{{ $system->nama_sistem }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Systems are automatically selected based on the selected Group</p>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description', $machineType->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Photo (Default untuk Machine Type)</label>
                    @if($machineType->photo)
                        <div class="mb-3">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Current Photo:</p>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 inline-block">
                                @php
                                    // Check for .webp version first
                                    $photoPath = $machineType->photo;
                                    $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $photoPath);
                                    $photoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath);
                                    $webpExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($webpPath);
                                    
                                    // Use webp if exists, otherwise use original
                                    $actualPath = $webpExists ? $webpPath : $photoPath;
                                    $photoUrl = asset('public-storage/' . $actualPath);
                                @endphp
                                @if($photoExists || $webpExists)
                                    <img src="{{ $photoUrl }}" 
                                         alt="Current Photo" 
                                         class="max-w-xs max-h-64 object-contain rounded border shadow-sm"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display:none;" class="text-sm text-red-500 mt-2">
                                        <p>Photo tidak dapat dimuat</p>
                                        <p class="text-xs">Path: {{ $actualPath }}</p>
                                    </div>
                                @else
                                    <div class="text-sm text-red-500">
                                        <p>Photo tidak ditemukan di storage</p>
                                        <p class="text-xs">Path: {{ $photoPath }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <input type="file" name="photo" id="photo" accept="image/*" class="w-full border rounded px-3 py-2 @error('photo') border-red-500 @enderror" onchange="previewPhoto(event)">
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 5MB). Kosongkan jika tidak ingin mengubah photo.</p>
                    <div id="photo_preview" class="hidden mt-3">
                        <p class="text-sm font-semibold text-gray-700 mb-2">New Photo Preview:</p>
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
                    <h3 class="text-md font-semibold text-gray-700 mb-3" x-text="editingPoint ? 'Edit Point' : 'Tambah Point Baru'"></h3>
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
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (menit)</label>
                            <input type="number" x-model="newPoint.duration" class="w-full border rounded px-3 py-2" min="0" placeholder="Waktu pengerjaan">
                            <p class="text-xs text-gray-500 mt-1">Waktu pengerjaan dalam menit</p>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi</label>
                            <textarea x-model="newPoint.instruction" class="w-full border rounded px-3 py-2" rows="2" placeholder="Masukkan instruksi"></textarea>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                            <input type="file" id="newPointPhoto" @change="handlePhotoChange($event)" accept="image/*" class="w-full border rounded px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 2MB)</p>
                            <div x-show="newPoint.photoPreview" class="mt-2">
                                <img :src="newPoint.photoPreview" alt="Preview" class="max-w-xs max-h-32 object-cover rounded border">
                            </div>
                        </div>
                        <div class="md:col-span-12 flex gap-2">
                            <button type="button" @click="saveNewPoint()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                                <span x-text="editingPoint ? 'Update Point' : 'Simpan Point'"></span>
                            </button>
                            <button type="button" @click="cancelAddPoint()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- List Points by Category -->
                <div class="space-y-6">
                    @foreach(['autonomous' => 'Autonomous Maintenance', 'preventive' => 'Preventive Maintenance', 'predictive' => 'Predictive Maintenance'] as $categoryKey => $categoryLabel)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg mb-2">
                                <h3 class="text-md font-semibold">{{ $categoryLabel }}</h3>
                            </div>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse(($points[$categoryKey] ?? collect()) as $point)
                                    <div class="bg-white rounded p-3 border border-gray-200">
                                        <div class="flex items-start gap-3">
                                            @if($point->photo)
                                                @php
                                                    $photoUrl = asset('public-storage/' . $point->photo);
                                                @endphp
                                                <div class="flex-shrink-0">
                                                    <img src="{{ $photoUrl }}" alt="Photo" class="w-12 h-20 object-cover rounded border cursor-pointer" onclick="openPhotoModal('{{ $photoUrl }}')" onerror="this.style.display='none';">
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-lg font-semibold text-gray-900">{{ $point->name }}</p>
                                                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $point->instruction ?? '-' }}</p>
                                                        <div class="flex items-center gap-3 mt-1 flex-wrap">
                                                            @if($point->frequency_type)
                                                                <span class="text-sm text-gray-400">Periode: {{ ucfirst($point->frequency_type) }} ({{ $point->frequency_value ?? 1 }}x)</span>
                                                            @endif
                                                            @if($categoryKey === 'predictive' && $point->standard)
                                                                <span class="text-sm text-blue-600 font-medium">Standard: {{ $point->standard->name }}</span>
                                                            @endif
                                                            <span class="text-sm text-gray-400">Urutan: {{ $point->sequence }}</span>
                                                            @if($point->duration)
                                                                <span class="text-sm text-gray-400">Duration: {{ $point->duration }} menit</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex gap-1 flex-shrink-0">
                                                        <button type="button" 
                                                            onclick="openEditModal({{ $point->id }}, '{{ $point->category }}', @js($point->name), @js($point->instruction ?? ''), {{ $point->sequence }}, '{{ $point->frequency_type ?? '' }}', {{ $point->frequency_value ?? 1 }}, '{{ $point->photo ? asset('public-storage/' . $point->photo) : '' }}', {{ $point->standard_id ?: 'null' }}, {{ $point->duration ?: 'null' }})"
                                                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs">
                                                            Edit
                                                        </button>
                                                        <form action="{{ route('machine-types.maintenance-points.destroy', $point->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus point ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-sm text-gray-500 py-4">
                                        Belum ada point {{ strtolower($categoryLabel) }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Update Machine Type</button>
                <a href="{{ route('machine-types.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Edit Point</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form method="POST" id="editForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" id="edit_category" class="w-full border rounded px-3 py-2" required onchange="toggleEditStandardField()">
                            <option value="autonomous">Autonomous Maintenance</option>
                            <option value="preventive">Preventive Maintenance</option>
                            <option value="predictive">Predictive Maintenance</option>
                        </select>
                    </div>
                    <div id="edit_standard_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Standard <span class="text-red-500">*</span></label>
                        <select name="standard_id" id="edit_standard_id" class="w-full border rounded px-3 py-2">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode Maintenance</label>
                        <select name="frequency_type" id="edit_frequency_type" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Periode</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Periode</label>
                        <input type="number" name="frequency_value" id="edit_frequency_value" class="w-full border rounded px-3 py-2" min="1" placeholder="1">
                        <p class="text-xs text-gray-500 mt-1">e.g., Setiap 2 minggu = 2</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Point <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                        <input type="number" name="sequence" id="edit_sequence" class="w-full border rounded px-3 py-2" min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (menit)</label>
                        <input type="number" name="duration" id="edit_duration" class="w-full border rounded px-3 py-2" min="0" placeholder="Waktu pengerjaan">
                        <p class="text-xs text-gray-500 mt-1">Waktu pengerjaan dalam menit</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi</label>
                        <textarea name="instruction" id="edit_instruction" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                        <input type="file" name="photo" id="edit_photo" accept="image/*" class="w-full border rounded px-3 py-2" onchange="previewEditPhoto(event)">
                        <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 2MB). Kosongkan jika tidak ingin mengubah foto.</p>
                        <div id="edit_photo_preview" class="mt-2 hidden">
                            <img id="edit_photo_img" src="" alt="Preview" class="max-w-xs max-h-32 object-cover rounded border">
                        </div>
                        <div id="edit_photo_current" class="mt-2">
                            <p class="text-xs text-gray-500">Foto saat ini:</p>
                            <img id="edit_photo_current_img" src="" alt="Current Photo" class="max-w-xs max-h-32 object-cover rounded border mt-1">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Modal with Zoom -->
<div id="photoModal" class="fixed inset-0 z-50 hidden overflow-hidden bg-black bg-opacity-90" onclick="closePhotoModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Zoom Controls -->
            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                <button onclick="zoomIn()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                <button onclick="zoomOut()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <button onclick="resetZoom()" class="bg-black bg-opacity-50 text-white hover:bg-opacity-70 rounded-full p-2 text-xs px-3">
                    Reset
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="overflow-auto w-full h-full flex items-center justify-center" id="photoModalContainer">
                <img id="photoModalImg" src="" alt="Photo" class="max-w-none transition-transform duration-200 cursor-move" style="transform-origin: center;">
            </div>
        </div>
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
            frequency_type: '',
            frequency_value: 1,
            name: '',
            instruction: '',
            sequence: 0,
            duration: '',
            photoFile: null,
            photoPreview: null
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
                duration: '',
                photoFile: null,
                photoPreview: null
            };
            document.getElementById('newPointPhoto').value = '';
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
            
            // Submit via AJAX to add point
            const formData = new FormData();
            formData.append('category', this.newPoint.category);
            if (this.newPoint.category === 'predictive' && this.newPoint.standard_id) {
                formData.append('standard_id', this.newPoint.standard_id);
            }
            formData.append('frequency_type', this.newPoint.frequency_type || '');
            formData.append('frequency_value', this.newPoint.frequency_value || 1);
            formData.append('name', this.newPoint.name);
            formData.append('instruction', this.newPoint.instruction || '');
            formData.append('sequence', this.newPoint.sequence || 0);
            formData.append('duration', this.newPoint.duration || '');
            if (this.newPoint.photoFile) {
                formData.append('photo', this.newPoint.photoFile);
            }
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route('machine-types.maintenance-points.store', $machineType->id) }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error adding point');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding point');
            });
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
                duration: '',
                photoFile: null,
                photoPreview: null
            };
            document.getElementById('newPointPhoto').value = '';
        },
        editPoint(point) {
            // This will be handled by the modal
        },
        removePoint(tempId) {
            // This will be handled by the form submission
        },
        getPointsByCategory(category) {
            return this.points.filter(p => p.category === category);
        }
    }
}

function toggleEditStandardField() {
    const category = document.getElementById('edit_category').value;
    const standardField = document.getElementById('edit_standard_field');
    const standardSelect = document.getElementById('edit_standard_id');
    if (category === 'predictive') {
        standardField.style.display = 'block';
        standardSelect.required = true;
    } else {
        standardField.style.display = 'none';
        standardSelect.required = false;
        standardSelect.value = '';
    }
}

function openEditModal(id, category, name, instruction, sequence, frequencyType = '', frequencyValue = 1, currentPhoto = '', standardId = '', duration = '') {
    document.getElementById('editForm').action = '{{ url('machine-types/maintenance-points') }}/' + id;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_instruction').value = instruction;
    document.getElementById('edit_sequence').value = sequence;
    document.getElementById('edit_duration').value = duration || '';
    document.getElementById('edit_frequency_type').value = frequencyType || '';
    document.getElementById('edit_frequency_value').value = frequencyValue || 1;
    
    // Toggle standard field first based on category
    toggleEditStandardField();
    
    // Then set standard_id if provided and valid (not null or empty string)
    // Convert to string for comparison and setting value
    const standardIdStr = String(standardId);
    if (standardIdStr && standardIdStr !== 'null' && standardIdStr !== 'undefined' && standardIdStr !== '') {
        const standardSelect = document.getElementById('edit_standard_id');
        // Check if the option exists before setting
        if (standardSelect.querySelector(`option[value="${standardIdStr}"]`)) {
            standardSelect.value = standardIdStr;
        }
    }
    document.getElementById('edit_photo').value = '';
    
    // Show current photo if exists
    const currentPhotoDiv = document.getElementById('edit_photo_current');
    const currentPhotoImg = document.getElementById('edit_photo_current_img');
    if (currentPhoto) {
        currentPhotoImg.src = currentPhoto;
        currentPhotoDiv.classList.remove('hidden');
    } else {
        currentPhotoDiv.classList.add('hidden');
    }
    
    // Hide preview
    document.getElementById('edit_photo_preview').classList.add('hidden');
    
    document.getElementById('editModal').classList.remove('hidden');
}

function previewEditPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('edit_photo_img').src = e.target.result;
            document.getElementById('edit_photo_preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('edit_photo_preview').classList.add('hidden');
    }
}

let currentZoom = 1;
const minZoom = 0.5;
const maxZoom = 5;
const zoomStep = 0.25;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

function openPhotoModal(photoUrl) {
    const modal = document.getElementById('photoModal');
    const img = document.getElementById('photoModalImg');
    const container = document.getElementById('photoModalContainer');
    
    img.src = photoUrl;
    currentZoom = 1;
    img.style.transform = `scale(${currentZoom})`;
    img.style.cursor = 'move';
    
    // Reset scroll position
    container.scrollLeft = 0;
    container.scrollTop = 0;
    
    modal.classList.remove('hidden');
    
    // Enable mouse wheel zoom
    container.addEventListener('wheel', handleWheelZoom, { passive: false });
    
    // Enable drag to pan
    img.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    const container = document.getElementById('photoModalContainer');
    
    modal.classList.add('hidden');
    
    // Remove event listeners
    container.removeEventListener('wheel', handleWheelZoom);
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
}

function handleWheelZoom(e) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    const newZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom + delta));
    setZoom(newZoom, e.clientX, e.clientY);
}

function zoomIn() {
    const newZoom = Math.min(maxZoom, currentZoom + zoomStep);
    setZoom(newZoom);
}

function zoomOut() {
    const newZoom = Math.max(minZoom, currentZoom - zoomStep);
    setZoom(newZoom);
}

function resetZoom() {
    setZoom(1);
    const container = document.getElementById('photoModalContainer');
    container.scrollLeft = 0;
    container.scrollTop = 0;
}

function setZoom(zoom, mouseX = null, mouseY = null) {
    const img = document.getElementById('photoModalImg');
    const container = document.getElementById('photoModalContainer');
    
    const oldZoom = currentZoom;
    currentZoom = zoom;
    img.style.transform = `scale(${currentZoom})`;
    
    if (mouseX && mouseY && oldZoom !== 1) {
        // Zoom towards mouse position
        const rect = container.getBoundingClientRect();
        const x = mouseX - rect.left;
        const y = mouseY - rect.top;
        
        const scrollX = x - (x - container.scrollLeft) * (zoom / oldZoom);
        const scrollY = y - (y - container.scrollTop) * (zoom / oldZoom);
        
        container.scrollLeft = scrollX;
        container.scrollTop = scrollY;
    } else {
        // Zoom from center
        const centerX = container.scrollLeft + container.clientWidth / 2;
        const centerY = container.scrollTop + container.clientHeight / 2;
        
        const scrollX = centerX - (centerX - container.scrollLeft) * (zoom / oldZoom);
        const scrollY = centerY - (centerY - container.scrollTop) * (zoom / oldZoom);
        
        container.scrollLeft = scrollX;
        container.scrollTop = scrollY;
    }
}

function startDrag(e) {
    if (currentZoom <= 1) return;
    isDragging = true;
    const container = document.getElementById('photoModalContainer');
    startX = e.pageX - container.offsetLeft;
    startY = e.pageY - container.offsetTop;
    scrollLeft = container.scrollLeft;
    scrollTop = container.scrollTop;
    e.preventDefault();
}

function drag(e) {
    if (!isDragging) return;
    e.preventDefault();
    const container = document.getElementById('photoModalContainer');
    const x = e.pageX - container.offsetLeft;
    const y = e.pageY - container.offsetTop;
    const walkX = (x - startX);
    const walkY = (y - startY);
    container.scrollLeft = scrollLeft - walkX;
    container.scrollTop = scrollTop - walkY;
}

function stopDrag() {
    isDragging = false;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
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

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
        closePhotoModal();
    }
});
</script>
@endpush
@endsection
