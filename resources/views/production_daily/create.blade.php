@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Tambah Hasil Produksi Perhari</h1>
            <p class="text-sm text-gray-600">Input data produksi langsung per hari untuk Room dengan Category Production</p>
        </div>
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('production-daily.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Plant Selection -->
                <div>
                    <label for="plant_name" class="block text-sm font-semibold text-gray-700 mb-2">Plant <span class="text-red-500">*</span></label>
                    <select name="plant_name" id="plant_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant_name') border-red-500 @enderror">
                        <option value="">-- Pilih Plant --</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant }}" {{ old('plant_name') == $plant ? 'selected' : '' }}>
                                {{ $plant }}
                            </option>
                        @endforeach
                    </select>
                    @error('plant_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Process Selection -->
                <div>
                    <label for="process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                    <select name="process_name" id="process_name" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_name') border-red-500 @enderror">
                        <option value="">-- Pilih Plant terlebih dahulu --</option>
                    </select>
                    @error('process_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Line Selection -->
                <div>
                    <label for="line_name" class="block text-sm font-semibold text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                    <select name="line_name" id="line_name" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_name') border-red-500 @enderror">
                        <option value="">-- Pilih Process terlebih dahulu --</option>
                    </select>
                    @error('line_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Room Selection -->
                <div>
                    <label for="room_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Room <span class="text-red-500">*</span></label>
                    <select name="room_erp_id" id="room_erp_id" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('room_erp_id') border-red-500 @enderror">
                        <option value="">-- Pilih Line terlebih dahulu --</option>
                    </select>
                    @error('room_erp_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Date -->
                <div class="md:col-span-2">
                    <label for="production_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('production_date') border-red-500 @enderror">
                    @error('production_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Work Hours Section -->
            <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Jam Kerja</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Target Produksi Per Jam -->
                    <div>
                        <label for="target_per_hour" class="block text-sm font-semibold text-gray-700 mb-2">Target Produksi Per Jam <span class="text-red-500">*</span></label>
                        <input type="number" name="target_per_hour" id="target_per_hour" value="{{ old('target_per_hour') }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('target_per_hour') border-red-500 @enderror" placeholder="Masukkan target per jam">
                        <p class="text-xs text-gray-500 mt-1">Target produksi dalam 1 jam</p>
                        @error('target_per_hour')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Masuk -->
                    <div>
                        <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Masuk <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('start_time') border-red-500 @enderror">
                        @error('start_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Pulang -->
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Pulang <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('end_time') border-red-500 @enderror">
                        @error('end_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Istirahat (Auto) -->
                    <div>
                        <label for="break_duration" class="block text-sm font-semibold text-gray-700 mb-2">Jam Istirahat</label>
                        <input type="text" id="break_duration_display" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-700" value="Akan dihitung otomatis">
                        <input type="hidden" name="break_duration" id="break_duration" value="">
                        <p class="text-xs text-gray-500 mt-1">
                            <span id="break_info">Senin-Kamis: 1 jam, Jumat: 1.5 jam</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Production Grades Section -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Hasil Produksi (Total per Hari)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Grade A -->
                    <div>
                        <label for="grade_a" class="block text-sm font-semibold text-gray-700 mb-2">Grade A <span class="text-red-500">*</span></label>
                        <input type="number" name="grade_a" id="grade_a" value="{{ old('grade_a') }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_a') border-red-500 @enderror" placeholder="Masukkan Grade A">
                        <p class="text-xs text-gray-500 mt-1">Total produksi Grade A per hari</p>
                        @error('grade_a')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Grade B -->
                    <div>
                        <label for="grade_b" class="block text-sm font-semibold text-gray-700 mb-2">Grade B (Defect)</label>
                        <input type="number" name="grade_b" id="grade_b" value="{{ old('grade_b', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_b') border-red-500 @enderror" placeholder="Masukkan Grade B">
                        <p class="text-xs text-gray-500 mt-1">Total defect Grade B per hari</p>
                        @error('grade_b')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Grade C -->
                    <div>
                        <label for="grade_c" class="block text-sm font-semibold text-gray-700 mb-2">Grade C (Defect)</label>
                        <input type="number" name="grade_c" id="grade_c" value="{{ old('grade_c', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_c') border-red-500 @enderror" placeholder="Masukkan Grade C">
                        <p class="text-xs text-gray-500 mt-1">Total defect Grade C per hari</p>
                        @error('grade_c')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Total Production Display -->
                <div class="mt-4 p-3 bg-white rounded border border-blue-300">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total Produksi:</span>
                        <span id="total_production_display" class="text-lg font-bold text-blue-600">0</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Total Produksi = Grade A + Grade B + Grade C</p>
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('production-daily.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plantSelect = document.getElementById('plant_name');
    const processSelect = document.getElementById('process_name');
    const lineSelect = document.getElementById('line_name');
    const roomSelect = document.getElementById('room_erp_id');
    const productionDate = document.getElementById('production_date');
    const breakDurationDisplay = document.getElementById('break_duration_display');
    const breakDuration = document.getElementById('break_duration');
    const breakInfo = document.getElementById('break_info');
    const gradeA = document.getElementById('grade_a');
    const gradeB = document.getElementById('grade_b');
    const gradeC = document.getElementById('grade_c');
    const totalDisplay = document.getElementById('total_production_display');

    // Calculate break duration based on day of week
    function calculateBreakDuration() {
        const dateValue = productionDate.value;
        if (!dateValue) {
            breakDurationDisplay.value = 'Akan dihitung otomatis';
            breakDuration.value = '';
            breakInfo.textContent = 'Senin-Kamis: 1 jam, Jumat: 1.5 jam';
            return;
        }

        const date = new Date(dateValue);
        const dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, ..., 5 = Friday, 6 = Saturday
        
        let breakHours = 1.0;
        let dayName = '';
        
        if (dayOfWeek === 5) { // Friday
            breakHours = 1.5;
            dayName = 'Jumat';
        } else if (dayOfWeek >= 1 && dayOfWeek <= 4) { // Monday to Thursday
            breakHours = 1.0;
            const dayNames = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
            dayName = dayNames[dayOfWeek];
        } else {
            // Weekend or Sunday
            breakHours = 1.0;
            dayName = dayOfWeek === 0 ? 'Minggu' : 'Sabtu';
        }

        breakDurationDisplay.value = `${breakHours} jam`;
        breakDuration.value = breakHours;
        breakInfo.textContent = `${dayName}: ${breakHours} jam`;
    }

    // Calculate total production
    function calculateTotal() {
        const a = parseInt(gradeA.value) || 0;
        const b = parseInt(gradeB.value) || 0;
        const c = parseInt(gradeC.value) || 0;
        const total = a + b + c;
        totalDisplay.textContent = total.toLocaleString('id-ID');
    }

    productionDate.addEventListener('change', calculateBreakDuration);
    gradeA.addEventListener('input', calculateTotal);
    gradeB.addEventListener('input', calculateTotal);
    gradeC.addEventListener('input', calculateTotal);

    // Initial calculation
    calculateBreakDuration();

    // Load processes when plant is selected
    plantSelect.addEventListener('change', function() {
        const plantName = this.value;
        
        // Reset dependent selects
        processSelect.innerHTML = '<option value="">-- Pilih Process --</option>';
        processSelect.disabled = true;
        lineSelect.innerHTML = '<option value="">-- Pilih Process terlebih dahulu --</option>';
        lineSelect.disabled = true;
        roomSelect.innerHTML = '<option value="">-- Pilih Line terlebih dahulu --</option>';
        roomSelect.disabled = true;

        if (plantName) {
            processSelect.disabled = false;
            processSelect.classList.remove('bg-gray-100');
            
            // Fetch processes
            fetch(`{{ route('production-daily.get-processes-by-plant') }}?plant_name=${encodeURIComponent(plantName)}`)
                .then(response => response.json())
                .then(data => {
                    processSelect.innerHTML = '<option value="">-- Pilih Process --</option>';
                    data.forEach(process => {
                        const option = document.createElement('option');
                        option.value = process.name;
                        option.textContent = process.name;
                        processSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading processes:', error);
                });
        } else {
            processSelect.classList.add('bg-gray-100');
        }
    });

    // Load lines when process is selected
    processSelect.addEventListener('change', function() {
        const plantName = plantSelect.value;
        const processName = this.value;
        
        // Reset dependent selects
        lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
        lineSelect.disabled = true;
        roomSelect.innerHTML = '<option value="">-- Pilih Line terlebih dahulu --</option>';
        roomSelect.disabled = true;

        if (plantName && processName) {
            lineSelect.disabled = false;
            lineSelect.classList.remove('bg-gray-100');
            
            // Fetch lines
            fetch(`{{ route('production-daily.get-lines-by-plant-and-process') }}?plant_name=${encodeURIComponent(plantName)}&process_name=${encodeURIComponent(processName)}`)
                .then(response => response.json())
                .then(data => {
                    lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
                    data.forEach(line => {
                        const option = document.createElement('option');
                        option.value = line.name;
                        option.textContent = line.name;
                        lineSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading lines:', error);
                });
        } else {
            lineSelect.classList.add('bg-gray-100');
        }
    });

    // Load rooms when line is selected
    lineSelect.addEventListener('change', function() {
        const plantName = plantSelect.value;
        const processName = processSelect.value;
        const lineName = this.value;
        
        // Reset room select
        roomSelect.innerHTML = '<option value="">-- Pilih Room --</option>';
        roomSelect.disabled = true;

        if (plantName && processName && lineName) {
            roomSelect.disabled = false;
            roomSelect.classList.remove('bg-gray-100');
            
            // Fetch rooms
            fetch(`{{ route('production-daily.get-rooms-by-plant-process-and-line') }}?plant_name=${encodeURIComponent(plantName)}&process_name=${encodeURIComponent(processName)}&line_name=${encodeURIComponent(lineName)}`)
                .then(response => response.json())
                .then(data => {
                    roomSelect.innerHTML = '<option value="">-- Pilih Room --</option>';
                    data.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        option.textContent = `${room.name}${room.kode_room ? ' (' + room.kode_room + ')' : ''}`;
                        roomSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                });
        } else {
            roomSelect.classList.add('bg-gray-100');
        }
    });

    // Initial calculation
    calculateTotal();
});
</script>
@endsection
