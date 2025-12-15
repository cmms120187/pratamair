@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Activity</h1>
            <p class="text-sm text-gray-600">Update activity information</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('activities.update', $activity->id) }}" method="POST" id="activityForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if(isset($page))
                <input type="hidden" name="page" value="{{ $page }}">
            @endif
            
            <div class="mb-4">
                <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" id="date" value="{{ old('date', $activity->date) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('date') border-red-500 @enderror">
                @error('date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
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
                                data-kode-room="{{ $roomErp->kode_room ?? '' }}"
                                @if($activity->kode_room == $roomErp->kode_room || ($activity->plant == $roomErp->plant_name && $activity->process == $roomErp->process_name && $activity->line == $roomErp->line_name && $activity->room_name == $roomErp->name)) selected @endif>
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
                    <label for="plant" class="block text-sm font-semibold text-gray-700 mb-2">Plant</label>
                    <input type="text" name="plant" id="plant" value="{{ old('plant', $activity->plant) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant') border-red-500 @enderror" placeholder="Akan terisi otomatis dari Room ERP" readonly>
                    @error('plant')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="process" class="block text-sm font-semibold text-gray-700 mb-2">Process</label>
                    <input type="text" name="process" id="process" value="{{ old('process', $activity->process) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process') border-red-500 @enderror" placeholder="Akan terisi otomatis dari Room ERP" readonly>
                    @error('process')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="line" class="block text-sm font-semibold text-gray-700 mb-2">Line</label>
                    <input type="text" name="line" id="line" value="{{ old('line', $activity->line) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line') border-red-500 @enderror" placeholder="Akan terisi otomatis dari Room ERP" readonly>
                    @error('line')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="room_name" class="block text-sm font-semibold text-gray-700 mb-2">Room Name</label>
                <input type="text" name="room_name" id="room_name" value="{{ old('room_name', $activity->room_name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('room_name') border-red-500 @enderror" placeholder="Akan terisi otomatis dari Room ERP" readonly>
                @error('room_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Hidden field for kode_room -->
            <input type="hidden" name="kode_room" id="kode_room" value="{{ old('kode_room', $activity->kode_room) }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="start" class="block text-sm font-semibold text-gray-700 mb-2">Start (hh:mm) <span class="text-red-500">*</span></label>
                    <input type="time" name="start" id="start" value="{{ old('start', $activity->start) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('start') border-red-500 @enderror">
                    @error('start')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="stop" class="block text-sm font-semibold text-gray-700 mb-2">Stop (hh:mm) <span class="text-red-500">*</span></label>
                    <input type="time" name="stop" id="stop" value="{{ old('stop', $activity->stop) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('stop') border-red-500 @enderror">
                    @error('stop')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="duration" class="block text-sm font-semibold text-gray-700 mb-2">Duration (mm)</label>
                    <input type="number" name="duration" id="duration" value="{{ old('duration', $activity->duration) }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('duration') border-red-500 @enderror" placeholder="Auto calculated">
                    @error('duration')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">Auto calculated from Start and Stop</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('description') border-red-500 @enderror" placeholder="Enter description">{{ old('description', $activity->description) }}</textarea>
                @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea name="remarks" id="remarks" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('remarks') border-red-500 @enderror" placeholder="Enter remarks">{{ old('remarks', $activity->remarks) }}</textarea>
                @error('remarks')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="relative">
                    <label for="mekanik_search" class="block text-sm font-semibold text-gray-700 mb-2">ID Mekanik / Nama Mekanik <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="mekanik_search" 
                           placeholder="Ketik NIK atau nama mekanik"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('id_mekanik') border-red-500 @enderror"
                           autocomplete="off">
                    <input type="hidden" name="id_mekanik" id="id_mekanik" value="{{ old('id_mekanik', $activity->id_mekanik) }}" required>
                    <input type="hidden" name="nama_mekanik" id="nama_mekanik" value="{{ old('nama_mekanik', $activity->nama_mekanik) }}" required>
                    
                    <!-- Suggestions dropdown -->
                    <div id="mekanik_dropdown" class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                        <!-- Suggestions will be populated by JavaScript -->
                    </div>
                    
                    <div id="selected_mekanik" class="mt-2 text-sm text-green-600 font-medium hidden">
                        <span id="selected_mekanik_info"></span>
                    </div>
                    
                    @error('id_mekanik')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    @error('nama_mekanik')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                
                <div class="relative">
                    <label for="machine_search" class="block text-sm font-semibold text-gray-700 mb-2">ID Mesin (Opsional)</label>
                    <div class="relative">
                        <input type="text" 
                               id="machine_search" 
                               placeholder="Ketik ID Mesin atau scan barcode/QR code..."
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-20 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('id_mesin') border-red-500 @enderror"
                               autocomplete="off">
                        <button type="button" 
                                id="scan_barcode_btn" 
                                onclick="openBarcodeModal()"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1"
                                title="Scan Barcode/QR Code">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Scan
                        </button>
                        <div id="machine_dropdown" class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto" style="top: 100%; left: 0; right: 0; width: 100%; max-width: 100%; max-height: 12rem; overflow-y: auto; box-sizing: border-box;">
                            <!-- Suggestions will be populated by JavaScript -->
                        </div>
                    </div>
                    <input type="hidden" name="id_mesin" id="id_mesin" value="{{ old('id_mesin', $activity->id_mesin) }}">
                    <div id="selected_machine" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Mesin Terpilih:</div>
                                <div id="selected_machine_info" class="text-sm text-gray-700"></div>
                            </div>
                            <button type="button" id="clear_machine" onclick="clearMachine()" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                        </div>
                    </div>
                    @error('id_mesin')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="photos" class="block text-sm font-semibold text-gray-700 mb-2">Photos (Maksimal 3 foto)</label>
                
                <!-- Show existing photos if any -->
                @if($activity->photos && count($activity->photos) > 0)
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Photo Saat Ini:</label>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            @foreach($activity->photos as $index => $photo)
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $photo) }}" alt="Photo {{ $index + 1 }}" class="w-full h-32 object-cover rounded border" onerror="this.src='{{ asset('images/placeholder.jpg') }}'; this.onerror=null;">
                                    <button type="button" onclick="removeExistingPhoto({{ $index }})" class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <input type="hidden" name="existing_photos[]" value="{{ $photo }}" id="existing_photo_{{ $index }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex gap-2 mb-2">
                    <label for="photos" class="flex-1 cursor-pointer">
                        <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white hover:bg-gray-50 text-center transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Pilih dari Galeri
                        </div>
                        <input type="file" 
                               name="photos[]" 
                               id="photos" 
                               accept="image/*"
                               multiple
                               class="hidden"
                               onchange="handlePhotoSelection(this)">
                    </label>
                    <button type="button" 
                            onclick="openCameraModal()" 
                            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 bg-white hover:bg-gray-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Ambil dari Kamera
                    </button>
                </div>
                
                @error('photos')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                @error('photos.*')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 5MB per foto). Maksimal 3 foto.</p>
                
                <!-- Photo previews -->
                <div id="photo_previews" class="mt-4 grid grid-cols-3 gap-4"></div>
            </div>

            <!-- Camera Modal -->
            <div id="camera_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">Ambil Foto dari Kamera</h3>
                            <button type="button" onclick="closeCameraModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <video id="camera_video" class="w-full rounded-lg bg-black" autoplay playsinline style="display: none;"></video>
                            <canvas id="camera_canvas" class="hidden"></canvas>
                            <div id="camera_placeholder" class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <p class="text-gray-500">Kamera akan dimuat...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="startCamera()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                Mulai Kamera
                            </button>
                            <button type="button" onclick="capturePhoto()" id="capture_btn" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition" style="display: none;">
                                Ambil Foto
                            </button>
                            <button type="button" onclick="stopCamera()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition" style="display: none;">
                                Stop
                            </button>
                            <button type="button" onclick="closeCameraModal()" class="text-gray-600 hover:text-gray-800">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barcode Scanner Modal -->
            <div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeBarcodeModal()"></div>
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">Scan Barcode/QR Code</h3>
                            <button onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <video id="barcode_video" class="w-full rounded-lg" autoplay playsinline></video>
                            <canvas id="barcode_canvas" class="hidden"></canvas>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Atau masukkan manual:</label>
                            <input type="text" 
                                   id="manual_barcode_input" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan ID Mesin">
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="startBarcodeScan()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Mulai Scan</button>
                            <button type="button" onclick="stopBarcodeScan()" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">Stop</button>
                            <button type="button" onclick="closeBarcodeModal()" class="text-gray-600 hover:text-gray-800">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Activity
                </button>
                <a href="{{ route('activities.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Mekanik data from server
const mekaniks = @json($mekaniks);

// Machine data from server
const machines = @json($machines);

// Room ERP dropdown handler
const roomErpSelect = document.getElementById('room_erp_select');
if (roomErpSelect) {
    roomErpSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            document.getElementById('plant').value = selectedOption.dataset.plant || '';
            document.getElementById('process').value = selectedOption.dataset.process || '';
            document.getElementById('line').value = selectedOption.dataset.line || '';
            document.getElementById('room_name').value = selectedOption.dataset.room || '';
            document.getElementById('kode_room').value = selectedOption.dataset.kodeRoom || '';
        } else {
            // Clear fields if no selection
            document.getElementById('plant').value = '';
            document.getElementById('process').value = '';
            document.getElementById('line').value = '';
            document.getElementById('room_name').value = '';
            document.getElementById('kode_room').value = '';
        }
    });
    
    // Auto-select Room ERP if values match on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentPlant = '{{ $activity->plant }}';
        const currentProcess = '{{ $activity->process }}';
        const currentLine = '{{ $activity->line }}';
        const currentRoomName = '{{ $activity->room_name }}';
        
        const currentKodeRoom = '{{ $activity->kode_room ?? '' }}';
        if (currentKodeRoom || currentPlant || currentProcess || currentLine || currentRoomName) {
            // Try to find matching Room ERP
            for (let i = 0; i < roomErpSelect.options.length; i++) {
                const option = roomErpSelect.options[i];
                if (option.value) {
                    // First try to match by kode_room
                    if (currentKodeRoom && option.dataset.kodeRoom === currentKodeRoom) {
                        roomErpSelect.value = option.value;
                        // Also set kode_room field
                        document.getElementById('kode_room').value = currentKodeRoom;
                        break;
                    }
                    // Fallback: match by plant, process, line, room
                    if (option.dataset.plant === currentPlant &&
                        option.dataset.process === currentProcess &&
                        option.dataset.line === currentLine &&
                        option.dataset.room === currentRoomName) {
                        roomErpSelect.value = option.value;
                        // Set kode_room if available
                        if (option.dataset.kodeRoom) {
                            document.getElementById('kode_room').value = option.dataset.kodeRoom;
                        }
                        break;
                    }
                }
            }
        }
    });
}

// DOM elements - Mekanik
const mekanikSearch = document.getElementById('mekanik_search');
const mekanikId = document.getElementById('id_mekanik');
const mekanikName = document.getElementById('nama_mekanik');
const mekanikDropdown = document.getElementById('mekanik_dropdown');
const selectedMekanik = document.getElementById('selected_mekanik');
const selectedMekanikInfo = document.getElementById('selected_mekanik_info');

// DOM elements - Machine
const machineSearch = document.getElementById('machine_search');
const machineIdInput = document.getElementById('id_mesin');
const machineDropdown = document.getElementById('machine_dropdown');
const selectedMachine = document.getElementById('selected_machine');
const selectedMachineInfo = document.getElementById('selected_machine_info');

// Existing photos tracking
let existingPhotos = @json($activity->photos ?? []);
let removedPhotoIndices = [];

// Mekanik search functionality
if (mekanikSearch) {
    let searchTimeout;
    
    mekanikSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (query.length < 2) {
                mekanikDropdown.classList.add('hidden');
                mekanikDropdown.innerHTML = '';
                return;
            }
            
            // Filter mekaniks
            const filtered = mekaniks.filter(m => {
                const nik = (m.nik || '').toLowerCase();
                const name = (m.name || '').toLowerCase();
                return nik.includes(query) || name.includes(query);
            }).slice(0, 8);
            
            if (filtered.length > 0) {
                mekanikDropdown.innerHTML = filtered.map(m => {
                    return `
                        <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100" 
                             data-nik="${m.nik || ''}" 
                             data-name="${m.name || ''}">
                            <div class="font-semibold text-gray-900">${m.nik || ''}</div>
                            <div class="text-sm text-gray-600">${m.name || ''}</div>
                        </div>
                    `;
                }).join('');
                
                // Add click event listeners
                mekanikDropdown.querySelectorAll('div[data-nik]').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const nik = this.getAttribute('data-nik');
                        const name = this.getAttribute('data-name');
                        selectMekanik(nik, name);
                    });
                });
                
                mekanikDropdown.classList.remove('hidden');
            } else {
                mekanikDropdown.classList.add('hidden');
                mekanikDropdown.innerHTML = '';
            }
        }, 300);
    });
    
    mekanikSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            mekanikDropdown.classList.remove('hidden');
        }
    });
}

// Select mekanik
function selectMekanik(nik, name) {
    mekanikId.value = nik || '';
    mekanikName.value = name || '';
    mekanikSearch.value = nik ? `${nik} - ${name}` : name;
    selectedMekanikInfo.textContent = nik ? `${nik} - ${name}` : name;
    selectedMekanik.classList.remove('hidden');
    mekanikDropdown.classList.add('hidden');
    mekanikSearch.blur();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (mekanikSearch && mekanikDropdown) {
        const isClickInside = mekanikSearch.contains(e.target) || mekanikDropdown.contains(e.target);
        if (!isClickInside) {
            mekanikDropdown.classList.add('hidden');
        }
    }
    if (machineSearch && machineDropdown) {
        const isClickInside = machineSearch.contains(e.target) || machineDropdown.contains(e.target);
        if (!isClickInside) {
            machineDropdown.classList.add('hidden');
        }
    }
});

// Machine search functionality
if (machineSearch) {
    machineSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            machineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = machines.filter(m => 
            (m.idMachine && m.idMachine.toLowerCase().includes(searchTerm)) ||
            (m.typeMachine && m.typeMachine.toLowerCase().includes(searchTerm)) ||
            (m.modelMachine && m.modelMachine.toLowerCase().includes(searchTerm)) ||
            (m.brandMachine && m.brandMachine.toLowerCase().includes(searchTerm))
        );
        
        if (filtered.length === 0) {
            machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
            machineDropdown.classList.remove('hidden');
            return;
        }
        
        machineDropdown.innerHTML = filtered.slice(0, 8).map(m => {
            return `
                <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors" 
                     data-machine-idmachine="${(m.idMachine || '').replace(/"/g, '&quot;')}"
                     data-machine-type="${(m.typeMachine || '').replace(/"/g, '&quot;')}"
                     data-machine-model="${(m.modelMachine || '').replace(/"/g, '&quot;')}"
                     data-machine-brand="${(m.brandMachine || '').replace(/"/g, '&quot;')}">
                    <div class="font-semibold text-gray-900">${m.idMachine || ''}</div>
                    <div class="text-xs text-gray-600">${m.typeMachine || ''} - ${m.brandMachine || ''} ${m.modelMachine || ''}</div>
                </div>
            `;
        }).join('');
        
        // Add click event listeners
        machineDropdown.querySelectorAll('[data-machine-idmachine]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const machineIdMachine = this.getAttribute('data-machine-idmachine');
                const machineType = this.getAttribute('data-machine-type');
                const machineModel = this.getAttribute('data-machine-model');
                const machineBrand = this.getAttribute('data-machine-brand');
                selectMachine(machineIdMachine, machineType, machineModel, machineBrand);
            });
        });
        
        machineDropdown.classList.remove('hidden');
    });
    
    machineSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            machineDropdown.classList.remove('hidden');
        }
    });
}

// Select machine
function selectMachine(idMachine, type, model, brand) {
    machineIdInput.value = idMachine || '';
    machineSearch.value = idMachine || '';
    selectedMachineInfo.innerHTML = `
        <div class="font-semibold">${idMachine || ''}</div>
        <div class="text-xs text-gray-600">${type || ''} - ${brand || ''} ${model || ''}</div>
    `;
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.blur();
}

// Clear machine
function clearMachine() {
    machineIdInput.value = '';
    machineSearch.value = '';
    selectedMachine.classList.add('hidden');
}

// Barcode Scanner functionality
let barcodeStream = null;
let barcodeVideo = null;
let barcodeCanvas = null;

function openBarcodeModal() {
    const modal = document.getElementById('barcode_modal');
    modal.classList.remove('hidden');
    barcodeVideo = document.getElementById('barcode_video');
    barcodeCanvas = document.getElementById('barcode_canvas');
}

function closeBarcodeModal() {
    stopBarcodeScan();
    const modal = document.getElementById('barcode_modal');
    modal.classList.add('hidden');
}

function startBarcodeScan() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' // Use back camera on mobile
            } 
        })
        .then(function(mediaStream) {
            barcodeStream = mediaStream;
            barcodeVideo.srcObject = mediaStream;
            barcodeVideo.style.display = 'block';
            
            // Manual input fallback
            const manualInput = document.getElementById('manual_barcode_input');
            if (manualInput) {
                manualInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const value = this.value.trim();
                        if (value) {
                            machineSearch.value = value;
                            machineIdInput.value = value;
                            // Try to find and select machine
                            const machine = machines.find(m => m.idMachine === value);
                            if (machine) {
                                selectMachine(machine.idMachine, machine.typeMachine, machine.modelMachine, machine.brandMachine);
                            } else {
                                selectedMachineInfo.innerHTML = `<div class="font-semibold">${value}</div>`;
                                selectedMachine.classList.remove('hidden');
                            }
                            closeBarcodeModal();
                        }
                    }
                });
            }
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
        });
    } else {
        alert('Browser tidak mendukung akses kamera.');
    }
}

function stopBarcodeScan() {
    if (barcodeStream) {
        barcodeStream.getTracks().forEach(track => track.stop());
        barcodeStream = null;
    }
    if (barcodeVideo) {
        barcodeVideo.srcObject = null;
        barcodeVideo.style.display = 'none';
    }
}

// Photo selection handler
let photoFiles = [];
let photoPreviews = [];

function handlePhotoSelection(input) {
    const files = Array.from(input.files);
    const previewsContainer = document.getElementById('photo_previews');
    
    // Calculate total photos (existing + new)
    const totalPhotos = existingPhotos.length + photoFiles.length;
    
    // Add new files to existing ones
    files.forEach(file => {
        if (file.type.startsWith('image/') && (totalPhotos + photoFiles.length) < 3) {
            photoFiles.push(file);
        }
    });
    
    const finalTotal = existingPhotos.length + photoFiles.length;
    if (finalTotal > 3) {
        alert('Maksimal 3 foto yang dapat diupload (termasuk foto yang sudah ada).');
        photoFiles = photoFiles.slice(0, 3 - existingPhotos.length);
        input.value = '';
        updatePhotoPreviews();
        return;
    }
    
    updatePhotoPreviews();
    updateFileInput();
}

function updatePhotoPreviews() {
    const previewsContainer = document.getElementById('photo_previews');
    previewsContainer.innerHTML = '';
    
    photoFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'relative';
            previewDiv.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}" class="w-full h-32 object-cover rounded border">
                <button type="button" onclick="removePhoto(${index})" class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            previewsContainer.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    });
}

function updateFileInput() {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    photoFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
}

// Remove photo
function removePhoto(index) {
    photoFiles.splice(index, 1);
    updatePhotoPreviews();
    updateFileInput();
}

// Remove existing photo
function removeExistingPhoto(index) {
    removedPhotoIndices.push(index);
    const photoElement = document.getElementById('existing_photo_' + index);
    if (photoElement) {
        photoElement.parentElement.remove();
    }
    // Remove from existingPhotos array
    existingPhotos.splice(index, 1);
    // Update all hidden inputs
    document.querySelectorAll('input[name="existing_photos[]"]').forEach((input, idx) => {
        input.id = 'existing_photo_' + idx;
    });
}

// Camera functionality
let stream = null;
let video = null;
let canvas = null;

function openCameraModal() {
    const modal = document.getElementById('camera_modal');
    modal.classList.remove('hidden');
    video = document.getElementById('camera_video');
    canvas = document.getElementById('camera_canvas');
}

function closeCameraModal() {
    stopCamera();
    const modal = document.getElementById('camera_modal');
    modal.classList.add('hidden');
}

function startCamera() {
    const placeholder = document.getElementById('camera_placeholder');
    const captureBtn = document.getElementById('capture_btn');
    const stopBtn = document.querySelector('button[onclick="stopCamera()"]');
    
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' // Use back camera on mobile
            } 
        })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            captureBtn.style.display = 'block';
            stopBtn.style.display = 'block';
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
        });
    } else {
        alert('Browser tidak mendukung akses kamera.');
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    if (video) {
        video.srcObject = null;
        video.style.display = 'none';
    }
    const placeholder = document.getElementById('camera_placeholder');
    const captureBtn = document.getElementById('capture_btn');
    const stopBtn = document.querySelector('button[onclick="stopCamera()"]');
    
    if (placeholder) placeholder.style.display = 'flex';
    if (captureBtn) captureBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'none';
}

function capturePhoto() {
    if (!video || !canvas) return;
    
    const totalPhotos = existingPhotos.length + photoFiles.length;
    if (totalPhotos >= 3) {
        alert('Maksimal 3 foto yang dapat diupload (termasuk foto yang sudah ada).');
        return;
    }
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    // Add watermark before converting to blob
    addWatermarkToCanvas(canvas, ctx).then(() => {
        // Convert canvas to blob after watermark is added
        canvas.toBlob(function(blob) {
            if (blob) {
                // Create a File object from blob
                const file = new File([blob], 'camera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                photoFiles.push(file);
                updatePhotoPreviews();
                updateFileInput();
            }
        }, 'image/jpeg', 0.9);
    });
}

// Function to add watermark to canvas
async function addWatermarkToCanvas(canvas, ctx) {
    return new Promise((resolve) => {
        // Get data from form
        const namaMekanik = document.getElementById('nama_mekanik')?.value || '';
        const idMekanik = document.getElementById('id_mekanik')?.value || '';
        const roomName = document.getElementById('room_name')?.value || '';
        const plant = document.getElementById('plant')?.value || '';
        const description = document.getElementById('description')?.value || '';
        
        // Get first 3 words from description
        const descriptionWords = description.trim().split(/\s+/).slice(0, 3).join(' ');
        
        // Prepare text lines
        const mekanikText = idMekanik && namaMekanik ? `${namaMekanik} / ${idMekanik}` : (namaMekanik || idMekanik || '');
        const locationText = roomName && plant ? `${roomName} / ${plant}` : (roomName || plant || '');
        const descText = descriptionWords || '';
        
        // Calculate watermark size (20% of photo width/height)
        const watermarkWidth = Math.min(canvas.width * 0.2, 300);
        const watermarkHeight = Math.min(canvas.height * 0.2, 200);
        
        // Position: left bottom (with padding)
        const padding = Math.max(10, canvas.width * 0.01);
        const watermarkX = padding;
        const watermarkY = canvas.height - watermarkHeight - padding;
        
        // Create watermark canvas
        const watermarkCanvas = document.createElement('canvas');
        watermarkCanvas.width = watermarkWidth;
        watermarkCanvas.height = watermarkHeight;
        const watermarkCtx = watermarkCanvas.getContext('2d');
        
        // Load logo
        const logoImg = new Image();
        logoImg.crossOrigin = 'anonymous';
        logoImg.onload = function() {
            // Clear watermark canvas
            watermarkCtx.clearRect(0, 0, watermarkWidth, watermarkHeight);
            
            // Set opacity to 50% for entire watermark
            watermarkCtx.globalAlpha = 0.5;
            
            // Draw logo (at top left of watermark area)
            const logoSize = Math.min(watermarkWidth * 0.25, watermarkHeight * 0.25, 40);
            const logoX = 0;
            const logoY = 0;
            watermarkCtx.drawImage(logoImg, logoX, logoY, logoSize, logoSize);
            
            // Set text properties with better sizing
            const fontSize = Math.max(8, Math.min(watermarkWidth * 0.04, 14));
            watermarkCtx.font = `bold ${fontSize}px Arial`;
            watermarkCtx.fillStyle = '#FFFFFF';
            watermarkCtx.strokeStyle = '#000000';
            watermarkCtx.lineWidth = 2;
            watermarkCtx.textAlign = 'left';
            watermarkCtx.textBaseline = 'top';
            
            // Calculate text positions
            let textY = logoSize + 5;
            const lineHeight = fontSize + 4;
            const maxTextWidth = watermarkWidth - 5;
            
            // Draw text with stroke for better visibility
            if (mekanikText) {
                // Truncate text if too long
                let text = mekanikText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
                textY += lineHeight;
            }
            
            if (locationText) {
                let text = locationText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
                textY += lineHeight;
            }
            
            if (descText) {
                let text = descText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
            }
            
            // Draw watermark onto main canvas with 50% opacity
            ctx.globalAlpha = 0.5;
            ctx.drawImage(watermarkCanvas, watermarkX, watermarkY);
            ctx.globalAlpha = 1.0;
            
            resolve();
        };
        
        logoImg.onerror = function() {
            // If logo fails to load, just draw text
            watermarkCtx.clearRect(0, 0, watermarkWidth, watermarkHeight);
            watermarkCtx.globalAlpha = 0.5;
            
            const fontSize = Math.max(8, Math.min(watermarkWidth * 0.04, 14));
            watermarkCtx.font = `bold ${fontSize}px Arial`;
            watermarkCtx.fillStyle = '#FFFFFF';
            watermarkCtx.strokeStyle = '#000000';
            watermarkCtx.lineWidth = 2;
            watermarkCtx.textAlign = 'left';
            watermarkCtx.textBaseline = 'top';
            
            let textY = 5;
            const lineHeight = fontSize + 4;
            const maxTextWidth = watermarkWidth - 5;
            
            if (mekanikText) {
                let text = mekanikText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
                textY += lineHeight;
            }
            
            if (locationText) {
                let text = locationText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
                textY += lineHeight;
            }
            
            if (descText) {
                let text = descText;
                const metrics = watermarkCtx.measureText(text);
                if (metrics.width > maxTextWidth) {
                    while (watermarkCtx.measureText(text + '...').width > maxTextWidth && text.length > 0) {
                        text = text.slice(0, -1);
                    }
                    text += '...';
                }
                watermarkCtx.strokeText(text, 0, textY);
                watermarkCtx.fillText(text, 0, textY);
            }
            
            ctx.globalAlpha = 0.5;
            ctx.drawImage(watermarkCanvas, watermarkX, watermarkY);
            ctx.globalAlpha = 1.0;
            
            resolve();
        };
        
        // Set logo source
        logoImg.src = '{{ asset("images/logo_tpm.png") }}';
    });
}

// Duration calculation
document.addEventListener('DOMContentLoaded', function() {
    const startInput = document.getElementById('start');
    const stopInput = document.getElementById('stop');
    const durationInput = document.getElementById('duration');
    
    function calculateDuration() {
        const start = startInput.value;
        const stop = stopInput.value;
        
        if (start && stop) {
            const startTime = new Date('2000-01-01T' + start + ':00');
            let stopTime = new Date('2000-01-01T' + stop + ':00');
            
            // Handle case where stop time is next day
            if (stopTime < startTime) {
                stopTime.setDate(stopTime.getDate() + 1);
            }
            
            const diffMs = stopTime - startTime;
            const diffMinutes = Math.floor(diffMs / 60000);
            
            durationInput.value = diffMinutes >= 0 ? diffMinutes : '';
        } else {
            durationInput.value = '';
        }
    }
    
    if (startInput && stopInput && durationInput) {
        startInput.addEventListener('change', calculateDuration);
        stopInput.addEventListener('change', calculateDuration);
        
        // Calculate on page load if values exist
        if (startInput.value && stopInput.value) {
            calculateDuration();
        }
    }
    
    // Pre-fill mekanik if values exist
    const currentIdMekanik = '{{ $activity->id_mekanik }}';
    const currentNamaMekanik = '{{ $activity->nama_mekanik }}';
    if (currentIdMekanik && currentNamaMekanik && mekanikSearch) {
        mekanikSearch.value = currentIdMekanik ? `${currentIdMekanik} - ${currentNamaMekanik}` : currentNamaMekanik;
        selectedMekanikInfo.textContent = currentIdMekanik ? `${currentIdMekanik} - ${currentNamaMekanik}` : currentNamaMekanik;
        selectedMekanik.classList.remove('hidden');
    }
    
    // Pre-fill machine if values exist
    const currentIdMesin = '{{ $activity->id_mesin }}';
    if (currentIdMesin && machineSearch) {
        machineSearch.value = currentIdMesin;
        machineIdInput.value = currentIdMesin;
        // Try to find machine in machines array
        const machine = machines.find(m => m.idMachine === currentIdMesin);
        if (machine) {
            selectedMachineInfo.innerHTML = `
                <div class="font-semibold">${machine.idMachine || ''}</div>
                <div class="text-xs text-gray-600">${machine.typeMachine || ''} - ${machine.brandMachine || ''} ${machine.modelMachine || ''}</div>
            `;
            selectedMachine.classList.remove('hidden');
        } else {
            selectedMachineInfo.innerHTML = `<div class="font-semibold">${currentIdMesin}</div>`;
            selectedMachine.classList.remove('hidden');
        }
    }
});
</script>
@endpush
@endsection

