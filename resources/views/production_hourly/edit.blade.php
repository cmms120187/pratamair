@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Hasil Produksi Per Jam</h1>
            <p class="text-sm text-gray-600">Edit data produksi per jam untuk Line dengan Process Production</p>
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
        
        <form action="{{ route('production-hourly.update', $productionHourly->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Line Selection -->
                <div>
                    <label for="line_id" class="block text-sm font-semibold text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                    <select name="line_id" id="line_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_id') border-red-500 @enderror">
                        <option value="">-- Pilih Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ old('line_id', $productionHourly->line_id) == $line->id ? 'selected' : '' }}>
                                {{ $line->name }} ({{ $line->process->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('line_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Process Selection -->
                <div>
                    <label for="process_id" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                    <select name="process_id" id="process_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_id') border-red-500 @enderror">
                        <option value="">-- Pilih Process --</option>
                        @foreach($processes as $process)
                            <option value="{{ $process->id }}" {{ old('process_id', $productionHourly->process_id) == $process->id ? 'selected' : '' }}>
                                {{ $process->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('process_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="production_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', $productionHourly->production_date->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('production_date') border-red-500 @enderror">
                    @error('production_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Hour -->
                <div>
                    <label for="hour" class="block text-sm font-semibold text-gray-700 mb-2">Jam (0-23) <span class="text-red-500">*</span></label>
                    <input type="number" name="hour" id="hour" value="{{ old('hour', $productionHourly->hour) }}" min="0" max="23" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('hour') border-red-500 @enderror" placeholder="0-23">
                    @error('hour')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Total Production -->
                <div>
                    <label for="total_production" class="block text-sm font-semibold text-gray-700 mb-2">Total Produksi <span class="text-red-500">*</span></label>
                    <input type="number" name="total_production" id="total_production" value="{{ old('total_production', $productionHourly->total_production) }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('total_production') border-red-500 @enderror">
                    @error('total_production')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Grade B -->
                <div>
                    <label for="grade_b" class="block text-sm font-semibold text-gray-700 mb-2">Grade B (Defect) <span class="text-red-500">*</span></label>
                    <input type="number" name="grade_b" id="grade_b" value="{{ old('grade_b', $productionHourly->grade_b) }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_b') border-red-500 @enderror">
                    @error('grade_b')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Grade C -->
                <div>
                    <label for="grade_c" class="block text-sm font-semibold text-gray-700 mb-2">Grade C (Defect) <span class="text-red-500">*</span></label>
                    <input type="number" name="grade_c" id="grade_c" value="{{ old('grade_c', $productionHourly->grade_c) }}" min="0" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('grade_c') border-red-500 @enderror">
                    @error('grade_c')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Grade A (Calculated) - Read Only -->
                <div>
                    <label for="grade_a_display" class="block text-sm font-semibold text-gray-700 mb-2">Grade A (Otomatis)</label>
                    <input type="text" id="grade_a_display" readonly value="{{ number_format($productionHourly->grade_a, 0, ',', '.') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">Grade A = Total Produksi - (Grade B + Grade C)</p>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('notes') border-red-500 @enderror">{{ old('notes', $productionHourly->notes) }}</textarea>
                    @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('production-hourly.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
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
    const totalProduction = document.getElementById('total_production');
    const gradeB = document.getElementById('grade_b');
    const gradeC = document.getElementById('grade_c');
    const gradeADisplay = document.getElementById('grade_a_display');

    function calculateGradeA() {
        const total = parseInt(totalProduction.value) || 0;
        const b = parseInt(gradeB.value) || 0;
        const c = parseInt(gradeC.value) || 0;
        const gradeA = Math.max(0, total - b - c);
        gradeADisplay.value = gradeA.toLocaleString('id-ID');
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
    calculateGradeA();
});
</script>
@endsection

