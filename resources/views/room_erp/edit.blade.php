@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Room ERP</h1>
            <p class="text-sm text-gray-600">Update room ERP information</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('room-erp.update', $roomErp->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="kode_room" class="block text-sm font-semibold text-gray-700 mb-2">Kode Room</label>
                <input type="text" 
                       name="kode_room" 
                       id="kode_room" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('kode_room') border-red-500 @enderror" 
                       value="{{ old('kode_room', $roomErp->kode_room) }}" 
                       placeholder="Enter kode room">
                @error('kode_room')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Room Name <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                       value="{{ old('name', $roomErp->name) }}" 
                       required
                       placeholder="Enter room name">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select name="category" id="category" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('category') border-red-500 @enderror">
                        <option value="">Select Category</option>
                        <option value="Production" {{ old('category', $roomErp->category) == 'Production' ? 'selected' : '' }}>Production</option>
                        <option value="Supporting" {{ old('category', $roomErp->category) == 'Supporting' ? 'selected' : '' }}>Supporting</option>
                        <option value="Warehouse" {{ old('category', $roomErp->category) == 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="Other" {{ old('category', $roomErp->category) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="plant_name" class="block text-sm font-semibold text-gray-700 mb-2">Plant Name</label>
                    <input type="text" 
                           name="plant_name" 
                           id="plant_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant_name') border-red-500 @enderror" 
                           value="{{ old('plant_name', $roomErp->plant_name) }}" 
                           placeholder="Auto-filled from Line"
                           readonly>
                    @error('plant_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="line_name" class="block text-sm font-semibold text-gray-700 mb-2">Line Name</label>
                    <select name="line_name" 
                            id="line_name" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_name') border-red-500 @enderror">
                        <option value="">-- Pilih Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->name }}" 
                                    data-plant="{{ $line->plant->name ?? '' }}"
                                    data-process="{{ $line->process->name ?? '' }}"
                                    {{ old('line_name', $roomErp->line_name) == $line->name ? 'selected' : '' }}>
                                {{ $line->name }}, {{ $line->plant->name ?? '-' }}-{{ $line->process->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    @error('line_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process Name</label>
                    <input type="text" 
                           name="process_name" 
                           id="process_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_name') border-red-500 @enderror" 
                           value="{{ old('process_name', $roomErp->process_name) }}" 
                           placeholder="Auto-filled from Line"
                           readonly>
                    @error('process_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="4" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('description') border-red-500 @enderror" 
                          placeholder="Enter description">{{ old('description', $roomErp->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Room ERP
                </button>
                <a href="{{ route('room-erp.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lineSelect = document.getElementById('line_name');
    const plantNameInput = document.getElementById('plant_name');
    const processNameInput = document.getElementById('process_name');

    // Auto-fill Plant Name and Process Name when Line is selected
    if (lineSelect) {
        lineSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const plantName = selectedOption.getAttribute('data-plant') || '';
                const processName = selectedOption.getAttribute('data-process') || '';
                
                if (plantNameInput) {
                    plantNameInput.value = plantName;
                }
                if (processNameInput) {
                    processNameInput.value = processName;
                }
            } else {
                // Clear if no line selected
                if (plantNameInput) {
                    plantNameInput.value = '';
                }
                if (processNameInput) {
                    processNameInput.value = '';
                }
            }
        });

        // Trigger change event on page load if line is already selected
        if (lineSelect.value) {
            lineSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endsection

