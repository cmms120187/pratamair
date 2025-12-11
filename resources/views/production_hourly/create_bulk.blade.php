@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Tambah Hasil Produksi Multiple Hours</h1>
            <p class="text-sm text-gray-600">Input data produksi untuk beberapa jam sekaligus (0-23) untuk Line dengan Process Production</p>
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
        
        <form action="{{ route('production-hourly.store') }}" method="POST" id="bulkForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
            </div>

            <!-- Hours Table -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Produksi Per Jam (0-23)</h3>
                    <div class="flex items-center gap-3">
                        <button type="button" id="btn_bulk_target" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Bulk Fill Target</button>
                        <button type="button" id="clearAllBtn" class="text-sm text-red-600 hover:text-red-800">Clear All</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Jam</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Target per Jam</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Grade A per Jam</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider bg-green-100 text-green-800">Grade A (Auto)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="hoursTableBody">
                            @for($hour = 0; $hour < 24; $hour++)
                            <tr class="hover:bg-gray-50" data-hour="{{ $hour }}">
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-semibold text-gray-900 border-r border-gray-200">
                                    {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                                    <input type="hidden" name="hours[{{ $hour }}][hour]" value="{{ $hour }}">
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap border-r border-gray-200">
                                    <input type="number" 
                                           name="hours[{{ $hour }}][target_per_hour]" 
                                           data-hour="{{ $hour }}"
                                           data-field="target"
                                           value="{{ old("hours.{$hour}.target_per_hour", '') }}"
                                           min="0" 
                                           class="w-full text-right border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hour-input">
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap border-r border-gray-200">
                                    <div class="flex gap-1">
                                        <input type="text" 
                                               name="hours[{{ $hour }}][total_production]" 
                                               data-hour="{{ $hour }}"
                                               data-field="total"
                                               value="{{ old("hours.{$hour}.total_production", '') }}"
                                               class="flex-1 text-right border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hour-input"
                                               placeholder="Grade A atau (istirahat)">
                                        <button type="button" 
                                                class="px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs rounded btn-istirahat" 
                                                data-hour="{{ $hour }}"
                                                title="Set (istirahat)">
                                            (istirahat)
                                        </button>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-semibold text-green-600 bg-green-50 grade-a-display" data-hour="{{ $hour }}">
                                    -
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td class="px-3 py-2 text-sm font-bold text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-gray-900 border-r border-gray-300" id="total-target-sum">0</td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-gray-900 border-r border-gray-300" id="total-production-sum">0</td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-green-600 bg-green-100" id="total-grade-a-sum">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Daily Grades Section (Per Hari) -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
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

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('production-hourly.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan Semua
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Fill Target Modal -->
<div id="bulk_target_modal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
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
    const hourInputs = document.querySelectorAll('.hour-input');
    const clearAllBtn = document.getElementById('clearAllBtn');

    // Button untuk set "(istirahat)" untuk setiap jam
    document.querySelectorAll('.btn-istirahat').forEach(btn => {
        btn.addEventListener('click', function() {
            const hour = this.getAttribute('data-hour');
            const row = document.querySelector(`tr[data-hour="${hour}"]`);
            const totalInput = row.querySelector('input[data-field="total"]');
            totalInput.value = '(istirahat)';
            calculateGradeA(hour);
        });
    });

    function calculateGradeA(hour) {
        const row = document.querySelector(`tr[data-hour="${hour}"]`);
        const totalInput = row.querySelector('input[data-field="total"]');
        const gradeADisplay = row.querySelector('.grade-a-display');
        const gradeBInput = document.getElementById('grade_b');
        const gradeCInput = document.getElementById('grade_c');

        const totalValue = totalInput.value.trim();
        
        // Jika "(istirahat)", tidak bisa hitung Grade A per jam
        if (totalValue === '(istirahat)') {
            gradeADisplay.textContent = '-';
            gradeADisplay.classList.remove('text-red-600');
            gradeADisplay.classList.add('text-green-600');
            calculateTotals();
            return;
        }
        
        // Coba parse sebagai angka
        const total = parseInt(totalValue) || 0;
        
        // Grade A per jam dihitung dari total produksi jam tersebut
        // Grade B dan C adalah total per hari, jadi tidak bisa dihitung per jam
        // Untuk display per jam, kita hanya tampilkan total produksi (atau bisa dikurangi dengan proporsi)
        gradeADisplay.textContent = total > 0 ? total.toLocaleString('id-ID') : '-';
        gradeADisplay.classList.remove('text-red-600');
        gradeADisplay.classList.add('text-green-600');

        calculateTotals();
    }

    function calculateTotals() {
        let totalTarget = 0;
        let totalProduction = 0;
        let totalGradeA = 0;
        const gradeBInput = document.getElementById('grade_b');
        const gradeCInput = document.getElementById('grade_c');
        const gradeB = parseInt(gradeBInput.value) || 0;
        const gradeC = parseInt(gradeCInput.value) || 0;

        for (let hour = 0; hour < 24; hour++) {
            const row = document.querySelector(`tr[data-hour="${hour}"]`);
            if (row) {
                const targetInput = row.querySelector('input[data-field="target"]');
                const totalInput = row.querySelector('input[data-field="total"]');
                const gradeADisplay = row.querySelector('.grade-a-display');

                const target = parseInt(targetInput.value) || 0;
                const totalValue = totalInput.value.trim();
                
                totalTarget += target;
                
                // Hitung total produksi (hanya yang angka, skip "(istirahat)")
                if (totalValue !== '(istirahat)' && totalValue !== '') {
                    const total = parseInt(totalValue) || 0;
                    totalProduction += total;
                    
                    // Parse Grade A dari display
                    const gradeAText = gradeADisplay.textContent.replace(/\./g, '').replace(/-/g, '0');
                    const gradeA = parseInt(gradeAText) || 0;
                    totalGradeA += gradeA;
                }
            }
        }

        // Grade A total = Total Produksi - (Grade B + Grade C)
        const calculatedGradeA = Math.max(0, totalProduction - gradeB - gradeC);

        document.getElementById('total-target-sum').textContent = totalTarget.toLocaleString('id-ID');
        document.getElementById('total-production-sum').textContent = totalProduction.toLocaleString('id-ID');
        document.getElementById('total-grade-a-sum').textContent = calculatedGradeA.toLocaleString('id-ID');
    }

    hourInputs.forEach(input => {
        input.addEventListener('input', function() {
            const hour = this.getAttribute('data-hour');
            if (hour !== null) {
                calculateGradeA(hour);
            } else {
                // Jika tidak ada data-hour, berarti grade_b atau grade_c (per hari)
                calculateTotals();
            }
        });
    });

    // Update totals ketika grade B atau C berubah
    const gradeBInput = document.getElementById('grade_b');
    const gradeCInput = document.getElementById('grade_c');
    if (gradeBInput) {
        gradeBInput.addEventListener('input', calculateTotals);
    }
    if (gradeCInput) {
        gradeCInput.addEventListener('input', calculateTotals);
    }

    clearAllBtn.addEventListener('click', function() {
        if (confirm('Yakin ingin menghapus semua input?')) {
            hourInputs.forEach(input => {
                input.value = '';
            });
            for (let hour = 0; hour < 24; hour++) {
                calculateGradeA(hour);
            }
        }
    });

    // Auto-filter process based on selected line
    const lineSelect = document.getElementById('line_id');
    const processSelect = document.getElementById('process_id') || document.querySelector('input[name="process_id"]');

    lineSelect.addEventListener('change', function() {
        const selectedLineId = this.value;
        if (selectedLineId) {
            const selectedOption = this.options[this.selectedIndex];
            const lineText = selectedOption.text;
            const match = lineText.match(/\(([^)]+)\)/);
            if (match) {
                const processName = match[1];
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
    for (let hour = 0; hour < 24; hour++) {
        calculateGradeA(hour);
    }

    // Bulk Fill Target Feature
    const btnBulkTarget = document.getElementById('btn_bulk_target');
    const bulkTargetModal = document.getElementById('bulk_target_modal');
    const btnCancelBulkTarget = document.getElementById('btn_cancel_bulk_target');
    const btnApplyBulkTarget = document.getElementById('btn_apply_bulk_target');
    const bulkTargetValue = document.getElementById('bulk_target_value');
    const bulkTargetDate = document.getElementById('bulk_target_date');
    const bulkTargetLine = document.getElementById('bulk_target_line');
    const productionDateInput = document.getElementById('production_date');
    const lineSelectForBulk = document.getElementById('line_id');
    // processSelect sudah dideklarasikan di atas, tidak perlu dideklarasikan lagi

    // Open modal
    if (btnBulkTarget && bulkTargetModal) {
        btnBulkTarget.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Bulk Fill Target button clicked');
            // Pre-fill dengan nilai yang sudah ada
            if (productionDateInput) {
                bulkTargetDate.value = productionDateInput.value;
            }
            if (lineSelectForBulk) {
                bulkTargetLine.value = lineSelectForBulk.value;
            }
            console.log('Showing modal');
            bulkTargetModal.style.display = 'block';
        });
    } else {
        console.error('btnBulkTarget or bulkTargetModal not found', {
            btnBulkTarget: !!btnBulkTarget,
            bulkTargetModal: !!bulkTargetModal
        });
    }

    // Close modal
    if (btnCancelBulkTarget && bulkTargetModal) {
        btnCancelBulkTarget.addEventListener('click', function() {
            bulkTargetModal.style.display = 'none';
            if (bulkTargetValue) {
                bulkTargetValue.value = '';
            }
        });
    }

    // Apply bulk target
    if (btnApplyBulkTarget) {
        btnApplyBulkTarget.addEventListener('click', async function() {
            const targetValue = bulkTargetValue.value.trim();
            const targetDate = bulkTargetDate.value;
            const targetLineId = bulkTargetLine.value;
            // processSelect sudah dideklarasikan di atas
            const processId = processSelect ? (processSelect.value || (processSelect.tagName === 'INPUT' ? processSelect.getAttribute('value') : null)) : null;

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
                    bulkTargetModal.style.display = 'none';
                    if (bulkTargetValue) {
                        bulkTargetValue.value = '';
                    }
                    
                    // Jika tanggal dan line sama dengan form saat ini, update semua target field di tabel
                    if (productionDateInput && lineSelectForBulk && 
                        targetDate === productionDateInput.value && targetLineId === lineSelectForBulk.value) {
                        for (let hour = 0; hour < 24; hour++) {
                            const row = document.querySelector(`tr[data-hour="${hour}"]`);
                            if (row) {
                                const targetInput = row.querySelector('input[data-field="target"]');
                                if (targetInput) {
                                    targetInput.value = target;
                                }
                            }
                        }
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
                bulkTargetModal.style.display = 'none';
            }
        });
    }
});
</script>
@endsection

