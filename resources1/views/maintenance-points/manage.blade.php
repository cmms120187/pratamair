@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kelola Point Maintenance</h1>
                <p class="text-gray-600 mt-1">{{ $machineType->name }}</p>
            </div>
            <div>
                <a href="{{ route('maintenance-points.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

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

        <!-- Form Tambah Point -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Point</h2>
            <form method="POST" action="{{ route('maintenance-points.store', $machineType->id) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" id="category" class="w-full border rounded px-3 py-2 @error('category') border-red-500 @enderror" required>
                            <option value="autonomous" {{ old('category') === 'autonomous' ? 'selected' : '' }}>Autonomous Maintenance</option>
                            <option value="preventive" {{ old('category') === 'preventive' ? 'selected' : '' }}>Preventive Maintenance</option>
                            <option value="predictive" {{ old('category') === 'predictive' ? 'selected' : '' }}>Predictive Maintenance</option>
                        </select>
                        @error('category')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-3" id="standard_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Standard <span class="text-red-500">*</span></label>
                        <select name="standard_id" id="standard_id" class="w-full border rounded px-3 py-2 @error('standard_id') border-red-500 @enderror">
                            <option value="">Pilih Standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ old('standard_id') == $standard->id ? 'selected' : '' }}>
                                    {{ $standard->name }} 
                                    @if($standard->min_value || $standard->max_value)
                                        ({{ $standard->min_value ?? '?' }}-{{ $standard->max_value ?? '?' }} {{ $standard->unit ?? '' }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('standard_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode Maintenance</label>
                        <select name="frequency_type" id="frequency_type" class="w-full border rounded px-3 py-2 @error('frequency_type') border-red-500 @enderror">
                            <option value="">Pilih Periode</option>
                            <option value="daily" {{ old('frequency_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('frequency_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('frequency_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency_type') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('frequency_type') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('frequency_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Periode</label>
                        <input type="number" name="frequency_value" id="frequency_value" class="w-full border rounded px-3 py-2 @error('frequency_value') border-red-500 @enderror" value="{{ old('frequency_value', 1) }}" min="1" placeholder="1">
                        <p class="text-xs text-gray-500 mt-1">e.g., Setiap 2 minggu = 2</p>
                        @error('frequency_value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Point <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                        <input type="number" name="sequence" class="w-full border rounded px-3 py-2 @error('sequence') border-red-500 @enderror" value="{{ old('sequence', 0) }}" min="0">
                        @error('sequence')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-12">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi</label>
                        <textarea name="instruction" class="w-full border rounded px-3 py-2 @error('instruction') border-red-500 @enderror" rows="2">{{ old('instruction') }}</textarea>
                        @error('instruction')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-12">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                            Tambah
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- List Points by Category -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach(['autonomous' => 'Autonomous Maintenance', 'preventive' => 'Preventive Maintenance', 'predictive' => 'Predictive Maintenance'] as $categoryKey => $categoryLabel)
                <div class="bg-white rounded-lg shadow">
                    <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg">
                        <h3 class="text-lg font-semibold">{{ $categoryLabel }}</h3>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Point</th>
                                        @if($categoryKey === 'predictive')
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Standard</th>
                                        @endif
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instruksi</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Urutan</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse(($points[$categoryKey] ?? collect()) as $idx => $point)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $idx + 1 }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $point->name }}</td>
                                            @if($categoryKey === 'predictive')
                                            <td class="px-3 py-2 text-sm text-gray-500">
                                                @if($point->standard)
                                                    <div class="flex flex-col">
                                                        <span class="font-medium">{{ $point->standard->name }}</span>
                                                        @if($point->standard->min_value || $point->standard->max_value)
                                                            <span class="text-xs text-gray-400">
                                                                {{ $point->standard->min_value ?? '?' }}-{{ $point->standard->max_value ?? '?' }} {{ $point->standard->unit ?? '' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            @endif
                                            <td class="px-3 py-2 text-sm text-gray-500">
                                                @if($point->frequency_type)
                                                    {{ ucfirst($point->frequency_type) }} ({{ $point->frequency_value }}x)
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ Str::limit($point->instruction ?? '-', 30) }}</td>
                                            <td class="px-3 py-2 text-center text-sm text-gray-500">{{ $point->sequence }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center">
                                                <button 
                                                    onclick="openEditModal({{ $point->id }}, '{{ $point->category }}', @js($point->name), @js($point->instruction ?? ''), {{ $point->sequence }}, '{{ $point->frequency_type ?? '' }}', {{ $point->frequency_value ?? 1 }}, {{ $point->standard_id ?? 'null' }})"
                                                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs mr-1">
                                                    Edit
                                                </button>
                                                <form action="{{ route('maintenance-points.destroy', $point->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus point ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada point {{ strtolower($categoryLabel) }}.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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
            <form method="POST" id="editForm">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instruksi</label>
                        <textarea name="instruction" id="edit_instruction" class="w-full border rounded px-3 py-2" rows="3"></textarea>
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

@push('scripts')
<script>
// Toggle standard field based on category
document.getElementById('category').addEventListener('change', function() {
    const standardField = document.getElementById('standard_field');
    const standardSelect = document.getElementById('standard_id');
    if (this.value === 'predictive') {
        standardField.style.display = 'block';
        standardSelect.required = true;
    } else {
        standardField.style.display = 'none';
        standardSelect.required = false;
        standardSelect.value = '';
    }
});

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

function openEditModal(id, category, name, instruction, sequence, frequencyType, frequencyValue, standardId) {
    document.getElementById('editForm').action = '{{ url('maintenance-points') }}/' + id;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_instruction').value = instruction;
    document.getElementById('edit_sequence').value = sequence;
    document.getElementById('edit_frequency_type').value = frequencyType || '';
    document.getElementById('edit_frequency_value').value = frequencyValue || 1;
    if (standardId) {
        document.getElementById('edit_standard_id').value = standardId;
    }
    toggleEditStandardField();
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const category = document.getElementById('category').value;
    if (category === 'predictive') {
        document.getElementById('standard_field').style.display = 'block';
    }
});
</script>
@endpush
@endsection

