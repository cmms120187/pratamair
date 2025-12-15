@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Tambah Hasil Produksi Per Jam</h1>
            <p class="text-sm text-gray-600">Input data produksi per jam untuk Line dengan Process Production</p>
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
        
        <form action="{{ route('production-hourly.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Line Selection -->
                <div>
                    <label for="line_id" class="block text-sm font-semibold text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                    <select name="line_id" id="line_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_id') border-red-500 @enderror">
                        <option value="">-- Pilih Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ old('line_id') == $line->id ? 'selected' : '' }}>
                                {{ $line->name }} ({{ $line->process->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('line_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Process Selection -->
                <div>
                    <label for="process_id" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                    @if(count($processes) == 1)
                        <input type="hidden" name="process_id" value="{{ $processes[0]->id }}">
                        <input type="text" value="{{ $processes[0]->name }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-700">
                    @else
                        <select name="process_id" id="process_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_id') border-red-500 @enderror">
                            <option value="">-- Pilih Process --</option>
                            @foreach($processes as $process)
                                <option value="{{ $process->id }}" {{ old('process_id') == $process->id ? 'selected' : '' }}>
                                    {{ $process->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('process_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="production_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('production_date') border-red-500 @enderror">
                    @error('production_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Hour -->
                <div>
                    <label for="hour" class="block text-sm font-semibold text-gray-700 mb-2">Jam (0-23) <span class="text-red-500">*</span></label>
                    <input type="number" name="hour" id="hour" value="{{ old('hour') }}" min="0" max="23" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('hour') border-red-500 @enderror" placeholder="0-23">
                    @error('hour')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Target per Hour -->
                <div>
                    <label for="target_per_hour" class="block text-sm font-semibold text-gray-700 mb-2">
                        Target per Jam
                        <button type="button" id="btn_bulk_target" class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">Bulk Fill</button>
                    </label>
                    <input type="number" name="target_per_hour" id="target_per_hour" value="{{ old('target_per_hour') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('target_per_hour') border-red-500 @enderror" placeholder="Target produksi per jam">
                    @error('target_per_hour')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Total Production (Grade A per Jam) -->
                <div>
                    <label for="total_production" class="block text-sm font-semibold text-gray-700 mb-2">Grade A per Jam <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" name="total_production" id="total_production" value="{{ old('total_production') }}" required class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('total_production') border-red-500 @enderror" placeholder="Angka atau (istirahat)">
                        <button type="button" id="btn_istirahat" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition">
                            (istirahat)
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Masukkan angka atau klik tombol untuk "(istirahat)"</p>
                    @error('total_production')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Daily Grades Section (Per Hari) -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Grade Defect (Total per Hari)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Grade B -->
                    <div>
                        <label for="grade_b" class="block text-sm font-semibold text-gray-700 mb-2">Grade B (Defect) - Total per Hari</label>
                        <input type="number" name="grade_b" id="grade_b" value="{{ old('grade_b', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_b') border-red-500 @enderror">
                        @error('grade_b')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Grade C -->
                    <div>
                        <label for="grade_c" class="block text-sm font-semibold text-gray-700 mb-2">Grade C (Defect) - Total per Hari</label>
                        <input type="number" name="grade_c" id="grade_c" value="{{ old('grade_c', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_c') border-red-500 @enderror">
                        @error('grade_c')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2">Catatan: Grade B dan Grade C adalah total untuk seluruh hari, bukan per jam</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Grade A (Calculated) - Read Only -->
                <div>
                    <label for="grade_a_display" class="block text-sm font-semibold text-gray-700 mb-2">Grade A (Otomatis)</label>
                    <input type="text" id="grade_a_display" readonly value="-" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">Grade A = Total Produksi - (Grade B + Grade C)</p>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('production-hourly.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Fill Modal -->
<div id="bulk_target_modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Bulk Fill Target per Jam</h3>
            <p class="text-sm text-gray-600 mb-4">Masukkan target yang akan diterapkan untuk semua jam (0-23) pada tanggal yang sama.</p>
            
            <div class="mb-4">
                <label for="bulk_target_value" class="block text-sm font-medium text-gray-700 mb-2">Target per Jam</label>
                <input type="number" id="bulk_target_value" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan target">
            </div>
            
            <div class="mb-4">
                <label for="bulk_target_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Produksi</label>
                <input type="date" id="bulk_target_date" value="{{ old('production_date', date('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-4">
                <label for="bulk_target_line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                <select id="bulk_target_line" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Line --</option>
                    @foreach($lines as $line)
                        <option value="{{ $line->id }}">{{ $line->name }} ({{ $line->process->name ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" id="btn_cancel_bulk_target" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Batal
                </button>
                <button type="button" id="btn_apply_bulk_target" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalProduction = document.getElementById('total_production');
    const gradeB = document.getElementById('grade_b');
    const gradeC = document.getElementById('grade_c');
    const gradeADisplay = document.getElementById('grade_a_display');

    // Button untuk set "(istirahat)"
    const btnIstirahat = document.getElementById('btn_istirahat');
    if (btnIstirahat) {
        btnIstirahat.addEventListener('click', function() {
            totalProduction.value = '(istirahat)';
            calculateGradeA();
        });
    }

    function calculateGradeA() {
        const totalValue = totalProduction.value.trim();
        
        // Jika "(istirahat)", tidak bisa hitung Grade A
        if (totalValue === '(istirahat)') {
            gradeADisplay.value = '-';
            return;
        }
        
        // Coba parse sebagai angka
        const total = parseInt(totalValue) || 0;
        const b = parseInt(gradeB.value) || 0;
        const c = parseInt(gradeC.value) || 0;
        
        // Grade A dihitung dari total produksi semua jam dalam 1 hari
        // Untuk single hour, kita hanya bisa hitung jika ada data semua jam
        // Untuk sekarang, kita hitung berdasarkan total produksi jam ini saja
        const gradeA = Math.max(0, total - b - c);
        gradeADisplay.value = gradeA > 0 ? gradeA.toLocaleString('id-ID') : '-';
    }

    totalProduction.addEventListener('input', calculateGradeA);
    gradeB.addEventListener('input', calculateGradeA);
    gradeC.addEventListener('input', calculateGradeA);

    // Auto-filter process based on selected line
    const lineSelect = document.getElementById('line_id');
    const processSelect = document.getElementById('process_id');

    lineSelect.addEventListener('change', function() {
        const selectedLineId = this.value;
        if (selectedLineId) {
            // Find the selected line and set its process
            const selectedOption = this.options[this.selectedIndex];
            const lineText = selectedOption.text;
            // Extract process name from line text (format: "Line Name (Process Name)")
            const match = lineText.match(/\(([^)]+)\)/);
            if (match) {
                const processName = match[1];
                // Find and select the matching process
                for (let i = 0; i < processSelect.options.length; i++) {
                    if (processSelect.options[i].text === processName) {
                        processSelect.value = processSelect.options[i].value;
                        break;
                    }
                }
            }
        }
    });

    // Initial calculation
    calculateGradeA();

    // Bulk Fill Target Feature
    const btnBulkTarget = document.getElementById('btn_bulk_target');
    const bulkTargetModal = document.getElementById('bulk_target_modal');
    const btnCancelBulkTarget = document.getElementById('btn_cancel_bulk_target');
    const btnApplyBulkTarget = document.getElementById('btn_apply_bulk_target');
    const bulkTargetValue = document.getElementById('bulk_target_value');
    const bulkTargetDate = document.getElementById('bulk_target_date');
    const bulkTargetLine = document.getElementById('bulk_target_line');
    const productionDateInput = document.getElementById('production_date');
    const lineSelect = document.getElementById('line_id');
    const processSelect = document.getElementById('process_id') || document.querySelector('input[name="process_id"]');

    // Open modal
    if (btnBulkTarget) {
        btnBulkTarget.addEventListener('click', function() {
            // Pre-fill dengan nilai yang sudah ada
            bulkTargetDate.value = productionDateInput.value;
            bulkTargetLine.value = lineSelect.value;
            bulkTargetValue.value = document.getElementById('target_per_hour').value || '';
            bulkTargetModal.classList.remove('hidden');
        });
    }

    // Close modal
    if (btnCancelBulkTarget) {
        btnCancelBulkTarget.addEventListener('click', function() {
            bulkTargetModal.classList.add('hidden');
            bulkTargetValue.value = '';
        });
    }

    // Apply bulk target
    if (btnApplyBulkTarget) {
        btnApplyBulkTarget.addEventListener('click', async function() {
            const targetValue = bulkTargetValue.value.trim();
            const targetDate = bulkTargetDate.value;
            const targetLineId = bulkTargetLine.value;
            const processId = processSelect ? (processSelect.value || processSelect.getAttribute('value')) : null;

            if (!targetValue || !targetDate || !targetLineId || !processId) {
                alert('Mohon lengkapi semua field: Target, Tanggal, Line, dan Process');
                return;
            }

            const target = parseInt(targetValue);
            if (isNaN(target) || target < 0) {
                alert('Target harus berupa angka positif');
                return;
            }

            // Konfirmasi
            if (!confirm(`Apakah Anda yakin ingin mengisi target ${target} untuk semua jam (0-23) pada tanggal ${targetDate} untuk Line yang dipilih?`)) {
                return;
            }

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                // Kirim request untuk bulk fill target
                const response = await fetch('{{ route("production-hourly.bulk-fill-target") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        line_id: targetLineId,
                        process_id: processId,
                        production_date: targetDate,
                        target_per_hour: target
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert(result.message || `Berhasil mengisi target untuk semua jam.`);
                    bulkTargetModal.classList.add('hidden');
                    bulkTargetValue.value = '';
                    
                    // Jika tanggal dan line sama dengan form saat ini, update target field
                    if (targetDate === productionDateInput.value && targetLineId === lineSelect.value) {
                        document.getElementById('target_per_hour').value = target;
                    }
                } else {
                    alert(result.message || 'Terjadi kesalahan saat mengisi target');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengisi target. Pastikan koneksi internet aktif.');
            }
        });
    }

    // Close modal when clicking outside
    if (bulkTargetModal) {
        bulkTargetModal.addEventListener('click', function(e) {
            if (e.target === bulkTargetModal) {
                bulkTargetModal.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection

