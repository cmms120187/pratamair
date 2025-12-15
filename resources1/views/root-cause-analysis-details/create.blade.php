@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Buat Root Cause Analysis</h1>
            <a href="{{ route('root-cause-analysis-details.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
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

        <form action="{{ route('root-cause-analysis-details.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf

            <!-- Basic Information -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Analisis <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="analysis_method" class="block text-sm font-medium text-gray-700 mb-1">Metode Analisis <span class="text-red-500">*</span></label>
                        <select name="analysis_method" id="analysis_method" required
                                class="w-full border rounded px-3 py-2 @error('analysis_method') border-red-500 @enderror"
                                onchange="toggleMethodForm()">
                            <option value="5_whys" {{ old('analysis_method', '5_whys') == '5_whys' ? 'selected' : '' }}>5 Whys</option>
                            <option value="fishbone" {{ old('analysis_method') == 'fishbone' ? 'selected' : '' }}>Fishbone (Ishikawa)</option>
                            <option value="fault_tree" {{ old('analysis_method') == 'fault_tree' ? 'selected' : '' }}>Fault Tree Analysis</option>
                            <option value="pareto" {{ old('analysis_method') == 'pareto' ? 'selected' : '' }}>Pareto Analysis</option>
                            <option value="combined" {{ old('analysis_method') == 'combined' ? 'selected' : '' }}>Kombinasi</option>
                        </select>
                        @error('analysis_method')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="analysis_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Analisis <span class="text-red-500">*</span></label>
                        <input type="date" name="analysis_date" id="analysis_date" value="{{ old('analysis_date', date('Y-m-d')) }}" required
                               class="w-full border rounded px-3 py-2 @error('analysis_date') border-red-500 @enderror">
                        @error('analysis_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="downtime_id" class="block text-sm font-medium text-gray-700 mb-1">Downtime (Opsional)</label>
                        <select name="downtime_id" id="downtime_id"
                                class="w-full border rounded px-3 py-2 @error('downtime_id') border-red-500 @enderror">
                            <option value="">Pilih Downtime</option>
                            @foreach($downtimes as $downtime)
                                <option value="{{ $downtime->id }}" {{ old('downtime_id', $selectedDowntime?->id) == $downtime->id ? 'selected' : '' }}>
                                    {{ $downtime->machine->idMachine ?? '-' }} - {{ $downtime->date->format('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('downtime_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-1">Mesin (Opsional)</label>
                        <select name="machine_id" id="machine_id"
                                class="w-full border rounded px-3 py-2 @error('machine_id') border-red-500 @enderror">
                            <option value="">Pilih Mesin</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ old('machine_id', $selectedDowntime?->machine_id) == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->idMachine }}
                                </option>
                            @endforeach
                        </select>
                        @error('machine_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required
                                class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror">
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="reviewed" {{ old('status') == 'reviewed' ? 'selected' : '' }}>Sudah Direview</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Problem Description -->
            <div class="mb-6">
                <label for="problem_description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Masalah <span class="text-red-500">*</span></label>
                <textarea name="problem_description" id="problem_description" rows="4" required
                          class="w-full border rounded px-3 py-2 @error('problem_description') border-red-500 @enderror">{{ old('problem_description', $selectedDowntime?->problem_description) }}</textarea>
                @error('problem_description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 5 Whys Form -->
            <div id="five_whys_form" class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">5 Whys Analysis</h2>
                <p class="text-sm text-gray-600 mb-4">Tanyakan "Mengapa?" secara berulang untuk menemukan akar masalah.</p>
                <div id="five_whys_container" class="space-y-4">
                    @for($i = 1; $i <= 5; $i++)
                    @php
                        $index = $i - 1;
                        $questionKey = "five_whys.{$index}.question";
                        $answerKey = "five_whys.{$index}.answer";
                    @endphp
                    <div class="border rounded-lg p-4 bg-gray-50 five-why-item">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-700">Why #{{ $i }}</h3>
                        </div>
                        <input type="hidden" name="five_whys[{{ $index }}][why_level]" value="{{ $i }}">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                            <input type="text" name="five_whys[{{ $index }}][question]"
                                   value="{{ old($questionKey, $i == 1 ? 'Mengapa masalah ini terjadi?' : '') }}"
                                   placeholder="Mengapa...?"
                                   class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban</label>
                            <textarea name="five_whys[{{ $index }}][answer]" rows="2"
                                      placeholder="Jawaban untuk Why #{{ $i }}"
                                      class="w-full border rounded px-3 py-2">{{ old($answerKey) }}</textarea>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Fishbone Form (Hidden by default) -->
            <div id="fishbone_form" class="mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Fishbone (Ishikawa) Diagram</h2>
                <p class="text-sm text-gray-600 mb-4">Coming soon - Fitur ini akan segera tersedia</p>
            </div>

            <!-- Fault Tree Form (Hidden by default) -->
            <div id="fault_tree_form" class="mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Fault Tree Analysis</h2>
                <p class="text-sm text-gray-600 mb-4">Coming soon - Fitur ini akan segera tersedia</p>
            </div>

            <!-- Root Cause & Actions -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Kesimpulan & Tindakan</h2>
                <div class="space-y-4">
                    <div>
                        <label for="root_cause" class="block text-sm font-medium text-gray-700 mb-1">Root Cause (Akar Masalah)</label>
                        <textarea name="root_cause" id="root_cause" rows="3"
                                  class="w-full border rounded px-3 py-2 @error('root_cause') border-red-500 @enderror">{{ old('root_cause') }}</textarea>
                        @error('root_cause')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="corrective_action" class="block text-sm font-medium text-gray-700 mb-1">Corrective Action (Tindakan Korektif)</label>
                        <textarea name="corrective_action" id="corrective_action" rows="3"
                                  class="w-full border rounded px-3 py-2 @error('corrective_action') border-red-500 @enderror">{{ old('corrective_action') }}</textarea>
                        @error('corrective_action')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="preventive_action" class="block text-sm font-medium text-gray-700 mb-1">Preventive Action (Tindakan Pencegahan)</label>
                        <textarea name="preventive_action" id="preventive_action" rows="3"
                                  class="w-full border rounded px-3 py-2 @error('preventive_action') border-red-500 @enderror">{{ old('preventive_action') }}</textarea>
                        @error('preventive_action')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('root-cause-analysis-details.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Simpan Analisis
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMethodForm() {
    const method = document.getElementById('analysis_method').value;

    // Hide all forms
    document.getElementById('five_whys_form').classList.add('hidden');
    document.getElementById('fishbone_form').classList.add('hidden');
    document.getElementById('fault_tree_form').classList.add('hidden');

    // Show selected form
    if (method === '5_whys' || method === 'combined') {
        document.getElementById('five_whys_form').classList.remove('hidden');
    }
    if (method === 'fishbone' || method === 'combined') {
        document.getElementById('fishbone_form').classList.remove('hidden');
    }
    if (method === 'fault_tree' || method === 'combined') {
        document.getElementById('fault_tree_form').classList.remove('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleMethodForm();
});
</script>
@endsection
