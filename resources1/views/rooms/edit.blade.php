@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Room</h1>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <form action="{{ route('rooms.update', $room->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Room Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $room->name) }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="plant_id" class="block text-sm font-medium text-gray-700 mb-2">Plant <span class="text-red-500">*</span></label>
                        <select name="plant_id" id="plant_id" class="w-full border rounded px-3 py-2 @error('plant_id') border-red-500 @enderror" required>
                            <option value="">Select Plant</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ old('plant_id', $room->plant_id) == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                            @endforeach
                        </select>
                        @error('plant_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                        <select name="line_id" id="line_id" class="w-full border rounded px-3 py-2 @error('line_id') border-red-500 @enderror" required>
                            <option value="">Select Line</option>
                            @foreach($lines as $line)
                                <option value="{{ $line->id }}" {{ old('line_id', $room->line_id) == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                            @endforeach
                        </select>
                        @error('line_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Line akan diperbarui saat Plant diubah</p>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" id="category" class="w-full border rounded px-3 py-2 @error('category') border-red-500 @enderror">
                            <option value="">Select Category</option>
                            <option value="Production" {{ old('category', $room->category) == 'Production' ? 'selected' : '' }}>Production</option>
                            <option value="Supporting" {{ old('category', $room->category) == 'Supporting' ? 'selected' : '' }}>Supporting</option>
                            <option value="Warehouse" {{ old('category', $room->category) == 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                            <option value="Other" {{ old('category', $room->category) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror">{{ old('description', $room->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('rooms.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">
                        Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plantSelect = document.getElementById('plant_id');
    const lineSelect = document.getElementById('line_id');
    const currentLineId = {{ old('line_id', $room->line_id ?? 'null') }};
    const currentPlantId = {{ old('plant_id', $room->plant_id ?? 'null') }};

    function loadLines(plantId, preserveSelection = false) {
        // Reset line dropdown
        lineSelect.innerHTML = '<option value="">Loading...</option>';
        lineSelect.disabled = true;
        lineSelect.classList.add('bg-gray-100');
        lineSelect.classList.remove('bg-white');
        
        if (plantId) {
            const url = `{{ route('rooms.get-lines-by-plant') }}?plant_id=${plantId}`;
            console.log('Fetching lines from:', url);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    // Get response as text first to see what we're dealing with
                    return response.text().then(text => {
                        console.log('Response text length:', text.length);
                        console.log('Response text:', text);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 200)}`);
                        }
                        
                        // Check if response is empty
                        if (!text || text.trim() === '') {
                            console.warn('Empty response received');
                            return [];
                        }
                        
                        // Try to parse as JSON
                        try {
                            const json = JSON.parse(text.trim());
                            console.log('Parsed JSON:', json);
                            return json;
                        } catch (e) {
                            console.error('Failed to parse JSON:', e);
                            console.error('Text that failed to parse:', text);
                            throw new Error('Response is not valid JSON. Response: ' + (text.length > 0 ? text.substring(0, 200) : '(empty)'));
                        }
                    });
                })
                .then(data => {
                    lineSelect.innerHTML = '<option value="">Select Line</option>';
                    
                    // Handle error response
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Ensure data is an array
                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    
                    if (data.length > 0) {
                        let hasSelected = false;
                        data.forEach(line => {
                            const option = document.createElement('option');
                            option.value = line.id;
                            option.textContent = line.name;
                            
                            // Preserve selection if plant hasn't changed or if old input exists
                            if (preserveSelection && (currentLineId && line.id == currentLineId)) {
                                option.selected = true;
                                hasSelected = true;
                            } else if (preserveSelection && '{{ old('line_id') }}' && line.id == '{{ old('line_id') }}') {
                                option.selected = true;
                                hasSelected = true;
                            }
                            
                            lineSelect.appendChild(option);
                        });
                        
                        // If no line was selected and we're preserving, select first one if only one option
                        if (preserveSelection && !hasSelected && data.length === 1) {
                            lineSelect.options[1].selected = true;
                        }
                        
                        lineSelect.disabled = false;
                        lineSelect.classList.remove('bg-gray-100');
                        lineSelect.classList.add('bg-white');
                    } else {
                        lineSelect.innerHTML = '<option value="">Tidak ada Line untuk Plant ini</option>';
                        lineSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching lines:', error);
                    lineSelect.innerHTML = '<option value="">Error loading lines</option>';
                    lineSelect.disabled = true;
                });
        } else {
            lineSelect.innerHTML = '<option value="">Pilih Plant terlebih dahulu</option>';
            lineSelect.disabled = true;
            lineSelect.classList.add('bg-gray-100');
            lineSelect.classList.remove('bg-white');
        }
    }

    // Load lines on page load if plant is selected
    if (plantSelect.value) {
        loadLines(plantSelect.value, true);
    }

    plantSelect.addEventListener('change', function() {
        // Don't preserve selection when plant changes
        loadLines(this.value, false);
    });
});
</script>
@endpush
@endsection

