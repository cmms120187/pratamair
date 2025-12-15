@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Update Preventive Maintenance Execution</h1>
            <a href="{{ route('preventive-maintenance.updating.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('preventive-maintenance.updating.update', $execution->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Execution Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Machine Info -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                        <div class="border rounded px-3 py-2 bg-gray-50">
                            <div class="font-semibold text-gray-900">{{ $execution->schedule->machine->idMachine ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $execution->schedule->machine->machineType->name ?? '-' }}</div>
                        </div>
                    </div>
                    
                    <!-- Scheduled Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date</label>
                        <div class="border rounded px-3 py-2 bg-gray-50">
                            {{ $execution->scheduled_date ? \Carbon\Carbon::parse($execution->scheduled_date)->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2 @error('status') border-red-500 @enderror" required>
                            <option value="pending" {{ old('status', $execution->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status', $execution->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $execution->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="skipped" {{ old('status', $execution->status) == 'skipped' ? 'selected' : '' }}>Skipped</option>
                            <option value="cancelled" {{ old('status', $execution->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Performed By -->
                    <div>
                        <label for="performed_by" class="block text-sm font-medium text-gray-700 mb-2">Performed By</label>
                        <select name="performed_by" id="performed_by" class="w-full border rounded px-3 py-2 @error('performed_by') border-red-500 @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('performed_by', $execution->performed_by) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('performed_by')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Actual Start Time -->
                    <div>
                        <label for="actual_start_time" class="block text-sm font-medium text-gray-700 mb-2">Actual Start Time</label>
                        <input type="datetime-local" 
                               name="actual_start_time" 
                               id="actual_start_time" 
                               value="{{ old('actual_start_time', $execution->actual_start_time ? \Carbon\Carbon::parse($execution->actual_start_time)->format('Y-m-d\TH:i') : '') }}" 
                               class="w-full border rounded px-3 py-2 @error('actual_start_time') border-red-500 @enderror">
                        @error('actual_start_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Actual End Time -->
                    <div>
                        <label for="actual_end_time" class="block text-sm font-medium text-gray-700 mb-2">Actual End Time</label>
                        <input type="datetime-local" 
                               name="actual_end_time" 
                               id="actual_end_time" 
                               value="{{ old('actual_end_time', $execution->actual_end_time ? \Carbon\Carbon::parse($execution->actual_end_time)->format('Y-m-d\TH:i') : '') }}" 
                               class="w-full border rounded px-3 py-2 @error('actual_end_time') border-red-500 @enderror">
                        @error('actual_end_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Cost -->
                    <div>
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-2">Cost</label>
                        <input type="number" 
                               name="cost" 
                               id="cost" 
                               step="0.01" 
                               min="0"
                               value="{{ old('cost', $execution->cost ?? '') }}" 
                               class="w-full border rounded px-3 py-2 @error('cost') border-red-500 @enderror">
                        @error('cost')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Checklist Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Checklist</h2>
                <div id="checklistContainer">
                    @if(!empty($checklist))
                        @foreach($checklist as $index => $item)
                            <div class="checklist-item mb-4 p-4 border rounded" data-index="{{ $index }}">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-1 flex items-center">
                                        <input type="checkbox" 
                                               name="checklist[{{ $index }}][checked]" 
                                               value="1" 
                                               {{ isset($item['checked']) && $item['checked'] ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </div>
                                    <div class="md:col-span-5">
                                        <input type="text" 
                                               name="checklist[{{ $index }}][item]" 
                                               value="{{ $item['item'] ?? '' }}" 
                                               placeholder="Checklist Item"
                                               class="w-full border rounded px-3 py-2" required>
                                    </div>
                                    <div class="md:col-span-5">
                                        <input type="text" 
                                               name="checklist[{{ $index }}][notes]" 
                                               value="{{ $item['notes'] ?? '' }}" 
                                               placeholder="Notes"
                                               class="w-full border rounded px-3 py-2">
                                    </div>
                                    <div class="md:col-span-1">
                                        <button type="button" onclick="removeChecklistItem(this)" class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="checklist-item mb-4 p-4 border rounded" data-index="0">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-1 flex items-center">
                                    <input type="checkbox" 
                                           name="checklist[0][checked]" 
                                           value="1" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-5">
                                    <input type="text" 
                                           name="checklist[0][item]" 
                                           value="" 
                                           placeholder="Checklist Item"
                                           class="w-full border rounded px-3 py-2" required>
                                </div>
                                <div class="md:col-span-5">
                                    <input type="text" 
                                           name="checklist[0][notes]" 
                                           value="" 
                                           placeholder="Notes"
                                           class="w-full border rounded px-3 py-2">
                                </div>
                                <div class="md:col-span-1">
                                    <button type="button" onclick="removeChecklistItem(this)" class="text-red-600 hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" onclick="addChecklistItem()" class="mt-4 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Checklist Item
                </button>
            </div>
            
            <!-- Findings and Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Findings & Actions</h2>
                <div class="grid grid-cols-1 gap-4">
                    <!-- Findings -->
                    <div>
                        <label for="findings" class="block text-sm font-medium text-gray-700 mb-2">Findings</label>
                        <textarea name="findings" 
                                  id="findings" 
                                  rows="4" 
                                  class="w-full border rounded px-3 py-2 @error('findings') border-red-500 @enderror">{{ old('findings', $execution->findings ?? '') }}</textarea>
                        @error('findings')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Actions Taken -->
                    <div>
                        <label for="actions_taken" class="block text-sm font-medium text-gray-700 mb-2">Actions Taken</label>
                        <textarea name="actions_taken" 
                                  id="actions_taken" 
                                  rows="4" 
                                  class="w-full border rounded px-3 py-2 @error('actions_taken') border-red-500 @enderror">{{ old('actions_taken', $execution->actions_taken ?? '') }}</textarea>
                        @error('actions_taken')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="3" 
                                  class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes', $execution->notes ?? '') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Photos Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Photos</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Photo Before -->
                    <div>
                        <label for="photo_before" class="block text-sm font-medium text-gray-700 mb-2">Photo Before</label>
                        @if($execution->photo_before)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $execution->photo_before) }}" alt="Photo Before" class="w-full h-48 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" 
                               name="photo_before" 
                               id="photo_before" 
                               accept="image/*"
                               class="w-full border rounded px-3 py-2 @error('photo_before') border-red-500 @enderror">
                        @error('photo_before')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Photo After -->
                    <div>
                        <label for="photo_after" class="block text-sm font-medium text-gray-700 mb-2">Photo After</label>
                        @if($execution->photo_after)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $execution->photo_after) }}" alt="Photo After" class="w-full h-48 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" 
                               name="photo_after" 
                               id="photo_after" 
                               accept="image/*"
                               class="w-full border rounded px-3 py-2 @error('photo_after') border-red-500 @enderror">
                        @error('photo_after')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('preventive-maintenance.updating.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Update Execution
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let checklistIndex = {{ !empty($checklist) ? count($checklist) : 1 }};

function addChecklistItem() {
    const container = document.getElementById('checklistContainer');
    const newItem = document.createElement('div');
    newItem.className = 'checklist-item mb-4 p-4 border rounded';
    newItem.setAttribute('data-index', checklistIndex);
    newItem.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-1 flex items-center">
                <input type="checkbox" 
                       name="checklist[${checklistIndex}][checked]" 
                       value="1" 
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            </div>
            <div class="md:col-span-5">
                <input type="text" 
                       name="checklist[${checklistIndex}][item]" 
                       value="" 
                       placeholder="Checklist Item"
                       class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="md:col-span-5">
                <input type="text" 
                       name="checklist[${checklistIndex}][notes]" 
                       value="" 
                       placeholder="Notes"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="md:col-span-1">
                <button type="button" onclick="removeChecklistItem(this)" class="text-red-600 hover:text-red-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    checklistIndex++;
}

function removeChecklistItem(button) {
    const item = button.closest('.checklist-item');
    item.remove();
}

// Auto set start/end time based on status change
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const startTimeInput = document.getElementById('actual_start_time');
    const endTimeInput = document.getElementById('actual_end_time');
    
    if (status === 'in_progress' && !startTimeInput.value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        startTimeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    if (status === 'completed' && !endTimeInput.value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        endTimeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
});
</script>
@endsection

