@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Hasil Produksi Perhari</h1>
            <p class="text-sm text-gray-600">Edit data produksi per hari untuk Room dengan Category Production</p>
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
        
        <form action="{{ route('production-daily.update', ['pdaily' => $productionDaily->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Plant Selection -->
                <div>
                    <label for="plant_name" class="block text-sm font-semibold text-gray-700 mb-2">Plant <span class="text-red-500">*</span></label>
                    <select name="plant_name" id="plant_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant_name') border-red-500 @enderror">
                        <option value="">-- Pilih Plant --</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant }}" {{ old('plant_name', $roomErp->plant_name ?? '') == $plant ? 'selected' : '' }}>
                                {{ $plant }}
                            </option>
                        @endforeach
                    </select>
                    @error('plant_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Process Selection -->
                <div>
                    <label for="process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                    <select name="process_name" id="process_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_name') border-red-500 @enderror">
                        <option value="">-- Pilih Process --</option>
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
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', $productionDaily->production_date->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('production_date') border-red-500 @enderror">
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
                        <input type="number" name="target_per_hour" id="target_per_hour" value="{{ old('target_per_hour', $productionDaily->target_per_hour) }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('target_per_hour') border-red-500 @enderror" placeholder="Masukkan target per jam">
                        <p class="text-xs text-gray-500 mt-1">Target produksi dalam 1 jam</p>
                        @error('target_per_hour')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Masuk -->
                    <div>
                        <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Masuk <span class="text-red-500">*</span></label>
                        @php
                            $startTimeValue = old('start_time');
                            if (!$startTimeValue && $productionDaily->start_time) {
                                // Format dari database (07:00:00) ke H:i (07:00)
                                $startTimeValue = substr($productionDaily->start_time, 0, 5);
                            }
                        @endphp
                        <input type="time" name="start_time" id="start_time" value="{{ $startTimeValue ?? '' }}" required step="60" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('start_time') border-red-500 @enderror">
                        @error('start_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Pulang -->
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Pulang <span class="text-red-500">*</span></label>
                        @php
                            $endTimeValue = old('end_time');
                            if (!$endTimeValue && $productionDaily->end_time) {
                                // Format dari database (16:00:00) ke H:i (16:00)
                                $endTimeValue = substr($productionDaily->end_time, 0, 5);
                            }
                        @endphp
                        <input type="time" name="end_time" id="end_time" value="{{ $endTimeValue ?? '' }}" required step="60" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('end_time') border-red-500 @enderror">
                        @error('end_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jam Istirahat (Auto) -->
                    <div>
                        <label for="break_duration" class="block text-sm font-semibold text-gray-700 mb-2">Jam Istirahat</label>
                        <input type="text" id="break_duration_display" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-700" value="{{ $productionDaily->break_duration ? $productionDaily->break_duration . ' jam' : '' }}">
                        <input type="hidden" name="break_duration" id="break_duration" value="{{ old('break_duration', $productionDaily->break_duration ?? '') }}">
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
                        <input type="number" name="grade_a" id="grade_a" value="{{ old('grade_a', $productionDaily->grade_a) }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_a') border-red-500 @enderror" placeholder="Masukkan Grade A">
                        <p class="text-xs text-gray-500 mt-1">Total produksi Grade A per hari</p>
                        @error('grade_a')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Grade B -->
                    <div>
                        <label for="grade_b" class="block text-sm font-semibold text-gray-700 mb-2">Grade B (Defect)</label>
                        <input type="number" name="grade_b" id="grade_b" value="{{ old('grade_b', $productionDaily->grade_b) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_b') border-red-500 @enderror" placeholder="Masukkan Grade B">
                        <p class="text-xs text-gray-500 mt-1">Total defect Grade B per hari</p>
                        @error('grade_b')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Grade C -->
                    <div>
                        <label for="grade_c" class="block text-sm font-semibold text-gray-700 mb-2">Grade C (Defect)</label>
                        <input type="number" name="grade_c" id="grade_c" value="{{ old('grade_c', $productionDaily->grade_c) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_c') border-red-500 @enderror" placeholder="Masukkan Grade C">
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

            <!-- Production Downtime Section -->
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Downtime Produksi & Kualitas</h3>
                    <button type="button" id="addDowntimeBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Downtime
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">Catat downtime terkait proses produksi, kualitas, material, changeover, dll (selain breakdown mesin)</p>
                
                <div id="downtimeContainer" class="space-y-4">
                    @if(isset($existingDowntimes) && $existingDowntimes->count() > 0)
                        @foreach($existingDowntimes as $index => $downtime)
                            @include('production_daily.partials.downtime-entry', ['downtime' => $downtime, 'index' => $index])
                        @endforeach
                    @endif
                </div>
                
                <div id="noDowntimeMessage" class="text-center py-4 text-gray-500 text-sm" style="{{ (isset($existingDowntimes) && $existingDowntimes->count() > 0) ? 'display: none;' : '' }}">
                    Belum ada downtime yang ditambahkan. Klik "Tambah Downtime" untuk menambahkan.
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                @php
                    // Get notes from ProductionHourly if exists
                    $notes = \App\Models\ProductionHourly::where('line_id', $productionDaily->line_id)
                        ->where('process_id', $productionDaily->process_id)
                        ->whereDate('production_date', $productionDaily->production_date)
                        ->where('hour', 0)
                        ->value('notes');
                @endphp
                <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('notes') border-red-500 @enderror">{{ old('notes', $notes ?? '') }}</textarea>
                @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('production-daily.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Update
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

    // Current values from server
    const currentPlant = '{{ $roomErp->plant_name ?? '' }}';
    const currentProcess = '{{ $roomErp->process_name ?? '' }}';
    const currentLine = '{{ $roomErp->line_name ?? '' }}';
    const currentRoomId = {{ $roomErp->id ?? 'null' }};

    // Calculate break duration based on day of week
    function calculateBreakDuration() {
        const dateValue = productionDate.value;
        const existingBreakDuration = breakDuration.value; // Get existing value from hidden input
        
        if (!dateValue) {
            if (existingBreakDuration) {
                breakDurationDisplay.value = `${existingBreakDuration} jam`;
            } else {
                breakDurationDisplay.value = 'Akan dihitung otomatis';
            }
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

        // Use existing value if available, otherwise calculate new
        const finalBreakHours = existingBreakDuration ? parseFloat(existingBreakDuration) : breakHours;
        breakDurationDisplay.value = `${finalBreakHours} jam`;
        breakDuration.value = finalBreakHours;
        breakInfo.textContent = `${dayName}: ${finalBreakHours} jam`;
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

    // Downtime Management
    const downtimeContainer = document.getElementById('downtimeContainer');
    const noDowntimeMessage = document.getElementById('noDowntimeMessage');
    const addDowntimeBtn = document.getElementById('addDowntimeBtn');
    let downtimeIndex = {{ isset($existingDowntimes) ? $existingDowntimes->count() : 0 }};

    const downtimeTypes = @json(\App\Models\ProductionDailyDowntime::getDowntimeTypes());

    // Initialize duration calculation for existing downtimes
    document.querySelectorAll('.downtime-start-time, .downtime-end-time').forEach(input => {
        const entry = input.closest('[data-index]');
        const startTimeInput = entry.querySelector('.downtime-start-time');
        const endTimeInput = entry.querySelector('.downtime-end-time');
        const durationInput = entry.querySelector('.downtime-duration');
        const durationMinutesInput = entry.querySelector('.downtime-duration-minutes');
        
        function calculateDuration() {
            const start = startTimeInput.value;
            const end = endTimeInput.value;
            
            if (start && end) {
                const [startHour, startMin] = start.split(':').map(Number);
                const [endHour, endMin] = end.split(':').map(Number);
                
                let startTotal = startHour * 60 + startMin;
                let endTotal = endHour * 60 + endMin;
                
                if (endTotal < startTotal) {
                    endTotal += 24 * 60;
                }
                
                const duration = endTotal - startTotal;
                durationInput.value = duration + ' menit';
                durationMinutesInput.value = duration;
            }
        }
        
        startTimeInput.addEventListener('change', calculateDuration);
        endTimeInput.addEventListener('change', calculateDuration);
    });

    // Remove button handlers for existing entries
    document.querySelectorAll('.removeDowntimeBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const entry = this.closest('[data-index]');
            entry.remove();
            updateDowntimeNumbers();
            if (downtimeContainer.children.length === 0) {
                noDowntimeMessage.style.display = 'block';
            }
        });
    });

    function addDowntimeEntry() {
        const entry = document.createElement('div');
        entry.className = 'bg-white p-4 rounded-lg border border-yellow-300';
        entry.dataset.index = downtimeIndex;
        
        entry.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-700">Downtime #${downtimeIndex + 1}</h4>
                <button type="button" class="removeDowntimeBtn text-red-600 hover:text-red-800 text-sm font-semibold">
                    Hapus
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Downtime <span class="text-red-500">*</span></label>
                    <select name="downtimes[${downtimeIndex}][downtime_type]" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Jenis --</option>
                        ${Object.entries(downtimeTypes).map(([key, value]) => `<option value="${key}">${value}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Include OEE</label>
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="downtimes[${downtimeIndex}][include_oee]" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Masukkan ke perhitungan OEE</label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="downtimes[${downtimeIndex}][start_time]" required class="downtime-start-time w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="downtimes[${downtimeIndex}][end_time]" required class="downtime-end-time w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Durasi (Menit)</label>
                    <input type="text" class="downtime-duration w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100" readonly>
                    <input type="hidden" name="downtimes[${downtimeIndex}][duration_minutes]" class="downtime-duration-minutes">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="downtimes[${downtimeIndex}][description]" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Keterangan downtime..."></textarea>
                </div>
            </div>
        `;
        
        downtimeContainer.appendChild(entry);
        noDowntimeMessage.style.display = 'none';
        
        const startTimeInput = entry.querySelector('.downtime-start-time');
        const endTimeInput = entry.querySelector('.downtime-end-time');
        const durationInput = entry.querySelector('.downtime-duration');
        const durationMinutesInput = entry.querySelector('.downtime-duration-minutes');
        
        function calculateDuration() {
            const start = startTimeInput.value;
            const end = endTimeInput.value;
            
            if (start && end) {
                const [startHour, startMin] = start.split(':').map(Number);
                const [endHour, endMin] = end.split(':').map(Number);
                
                let startTotal = startHour * 60 + startMin;
                let endTotal = endHour * 60 + endMin;
                
                if (endTotal < startTotal) {
                    endTotal += 24 * 60;
                }
                
                const duration = endTotal - startTotal;
                durationInput.value = duration + ' menit';
                durationMinutesInput.value = duration;
            }
        }
        
        startTimeInput.addEventListener('change', calculateDuration);
        endTimeInput.addEventListener('change', calculateDuration);
        
        entry.querySelector('.removeDowntimeBtn').addEventListener('click', function() {
            entry.remove();
            updateDowntimeNumbers();
            if (downtimeContainer.children.length === 0) {
                noDowntimeMessage.style.display = 'block';
            }
        });
        
        downtimeIndex++;
    }

    function updateDowntimeNumbers() {
        const entries = downtimeContainer.querySelectorAll('[data-index]');
        entries.forEach((entry, index) => {
            entry.querySelector('h4').textContent = `Downtime #${index + 1}`;
        });
    }

    addDowntimeBtn.addEventListener('click', addDowntimeEntry);

    // Load processes when plant is selected
    function loadProcesses(plantName) {
        if (!plantName) {
            processSelect.innerHTML = '<option value="">-- Pilih Process --</option>';
            processSelect.disabled = true;
            return;
        }

        processSelect.disabled = false;
        processSelect.classList.remove('bg-gray-100');
        
        fetch(`{{ route('production-daily.get-processes-by-plant') }}?plant_name=${encodeURIComponent(plantName)}`)
            .then(response => response.json())
            .then(data => {
                processSelect.innerHTML = '<option value="">-- Pilih Process --</option>';
                data.forEach(process => {
                    const option = document.createElement('option');
                    option.value = process.name;
                    option.textContent = process.name;
                    if (process.name === currentProcess) {
                        option.selected = true;
                    }
                    processSelect.appendChild(option);
                });
                
                // Auto-load lines if process matches current
                if (currentProcess && processSelect.value === currentProcess) {
                    loadLines(plantName, currentProcess);
                }
            })
            .catch(error => {
                console.error('Error loading processes:', error);
            });
    }

    // Load lines when process is selected
    function loadLines(plantName, processName) {
        if (!plantName || !processName) {
            lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
            lineSelect.disabled = true;
            return;
        }

        lineSelect.disabled = false;
        lineSelect.classList.remove('bg-gray-100');
        
        fetch(`{{ route('production-daily.get-lines-by-plant-and-process') }}?plant_name=${encodeURIComponent(plantName)}&process_name=${encodeURIComponent(processName)}`)
            .then(response => response.json())
            .then(data => {
                lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
                data.forEach(line => {
                    const option = document.createElement('option');
                    option.value = line.name;
                    option.textContent = line.name;
                    if (line.name === currentLine) {
                        option.selected = true;
                    }
                    lineSelect.appendChild(option);
                });
                
                // Auto-load rooms if line matches current
                if (currentLine && lineSelect.value === currentLine) {
                    loadRooms(plantName, processName, currentLine);
                }
            })
            .catch(error => {
                console.error('Error loading lines:', error);
            });
    }

    // Load rooms when line is selected
    function loadRooms(plantName, processName, lineName) {
        if (!plantName || !processName || !lineName) {
            roomSelect.innerHTML = '<option value="">-- Pilih Room --</option>';
            roomSelect.disabled = true;
            return;
        }

        roomSelect.disabled = false;
        roomSelect.classList.remove('bg-gray-100');
        
        fetch(`{{ route('production-daily.get-rooms-by-plant-process-and-line') }}?plant_name=${encodeURIComponent(plantName)}&process_name=${encodeURIComponent(processName)}&line_name=${encodeURIComponent(lineName)}`)
            .then(response => response.json())
            .then(data => {
                roomSelect.innerHTML = '<option value="">-- Pilih Room --</option>';
                data.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `${room.name}${room.kode_room ? ' (' + room.kode_room + ')' : ''}`;
                    if (room.id == currentRoomId) {
                        option.selected = true;
                    }
                    roomSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading rooms:', error);
            });
    }

    // Event listeners
    plantSelect.addEventListener('change', function() {
        const plantName = this.value;
        
        // Reset dependent selects
        processSelect.innerHTML = '<option value="">-- Pilih Process --</option>';
        processSelect.disabled = !plantName;
        if (!plantName) {
            processSelect.classList.add('bg-gray-100');
        }
        lineSelect.innerHTML = '<option value="">-- Pilih Process terlebih dahulu --</option>';
        lineSelect.disabled = true;
        lineSelect.classList.add('bg-gray-100');
        roomSelect.innerHTML = '<option value="">-- Pilih Line terlebih dahulu --</option>';
        roomSelect.disabled = true;
        roomSelect.classList.add('bg-gray-100');

        if (plantName) {
            loadProcesses(plantName);
        }
    });

    processSelect.addEventListener('change', function() {
        const plantName = plantSelect.value;
        const processName = this.value;
        
        // Reset dependent selects
        lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
        lineSelect.disabled = !(plantName && processName);
        if (!(plantName && processName)) {
            lineSelect.classList.add('bg-gray-100');
        }
        roomSelect.innerHTML = '<option value="">-- Pilih Line terlebih dahulu --</option>';
        roomSelect.disabled = true;
        roomSelect.classList.add('bg-gray-100');

        if (plantName && processName) {
            loadLines(plantName, processName);
        }
    });

    lineSelect.addEventListener('change', function() {
        const plantName = plantSelect.value;
        const processName = processSelect.value;
        const lineName = this.value;
        
        // Reset room select
        roomSelect.innerHTML = '<option value="">-- Pilih Room --</option>';
        roomSelect.disabled = !(plantName && processName && lineName);
        if (!(plantName && processName && lineName)) {
            roomSelect.classList.add('bg-gray-100');
        }

        if (plantName && processName && lineName) {
            loadRooms(plantName, processName, lineName);
        }
    });

    // Initialize on page load
    if (currentPlant) {
        loadProcesses(currentPlant);
    }

    // Initial calculation for break duration and total
    calculateBreakDuration();
    calculateTotal();
});
</script>
@endsection
