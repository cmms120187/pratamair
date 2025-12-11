@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Reason</h1>
            <a href="{{ route('reasons.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
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

        <form action="{{ route('reasons.update', $reason->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <div>
                    <label for="system_select" class="block text-sm font-medium text-gray-700 mb-2">System <span class="text-red-500">*</span></label>
                    <select name="system_select" id="system_select" required disabled class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed @error('system_select') border-red-500 @enderror">
                        <option value="">-- Pilih System --</option>
                        @foreach($systems ?? [] as $system)
                            <option value="{{ $system['id'] }}" {{ ($currentSystemId ?? old('system_select')) == $system['id'] ? 'selected' : '' }}>{{ $system['nama_sistem'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">System terkunci (tidak dapat diubah saat edit)</p>
                    @error('system_select')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="problem_select" class="block text-sm font-medium text-gray-700 mb-2">Problem <span class="text-red-500">*</span></label>
                    <select name="problem_select" id="problem_select" required disabled class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed @error('problem_select') border-red-500 @enderror">
                        <option value="">-- Pilih Problem --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Problem terkunci (tidak dapat diubah saat edit)</p>
                    @error('problem_select')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Reason Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $reason->name) }}" placeholder="e.g., GANTI SPARE PART" readonly class="w-full border rounded px-3 py-2 bg-gray-50 @error('name') border-red-500 @enderror" required>
                    <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari problem yang dipilih (dapat diedit)</p>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end gap-4">
                    <a href="{{ route('reasons.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded shadow transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow transition">
                        Update Reason
                    </button>
                </div>
            </div>
        </form>
        
        <script>
        // System and Problem data from server (already mapped in controller)
        const systems = @json($systems ?? []);
        const problems = @json($problems ?? []);
        
        // System and Problem filtering functionality
        const systemSelect = document.getElementById('system_select');
        const problemSelect = document.getElementById('problem_select');
        const reasonName = document.getElementById('name');
        
        // Filter problems based on selected system (client-side filtering)
        if (systemSelect) {
            systemSelect.addEventListener('change', function() {
                const selectedSystemId = this.value;
                
                // Clear problem and reason
                problemSelect.innerHTML = '<option value="">-- Pilih Problem --</option>';
                problemSelect.value = '';
                if (reasonName) {
                    // Keep existing value if no system selected, but make it readonly
                    if (!selectedSystemId) {
                        reasonName.readOnly = true;
                        reasonName.classList.add('bg-gray-50');
                    }
                }
                
                if (!selectedSystemId) {
                    // Disable problem select if no system selected
                    problemSelect.disabled = true;
                    problemSelect.classList.add('bg-gray-100');
                    if (reasonName) {
                        reasonName.readOnly = true;
                        reasonName.classList.add('bg-gray-50');
                    }
                    return;
                }
                
                // Filter problems that belong to the selected system
                const filteredProblems = problems.filter(problem => {
                    return problem.system_ids && problem.system_ids.includes(selectedSystemId);
                });
                
                if (filteredProblems.length === 0) {
                    problemSelect.innerHTML = '<option value="">-- Tidak ada problem untuk system ini --</option>';
                    problemSelect.disabled = true;
                    problemSelect.classList.add('bg-gray-100');
                    if (reasonName) {
                        reasonName.readOnly = true;
                        reasonName.classList.add('bg-gray-50');
                    }
                    return;
                }
                
                // Populate problem dropdown
                filteredProblems.forEach(problem => {
                    const option = document.createElement('option');
                    option.value = problem.id;
                    option.textContent = problem.name + (problem.problem_header ? ' (' + problem.problem_header + ')' : '');
                    option.setAttribute('data-problem-name', problem.name);
                    problemSelect.appendChild(option);
                });
                
                // Enable problem select
                problemSelect.disabled = false;
                problemSelect.classList.remove('bg-gray-100');
                if (reasonName) {
                    reasonName.readOnly = true;
                    reasonName.classList.add('bg-gray-50');
                }
            });
        }
        
        // Auto-fill reason when problem is selected
        if (problemSelect) {
            problemSelect.addEventListener('change', function() {
                const selectedProblemId = this.value;
                
                if (!reasonName) return;
                
                if (!selectedProblemId) {
                    // Don't clear existing value, just make it readonly
                    reasonName.readOnly = true;
                    reasonName.classList.add('bg-gray-50');
                    return;
                }
                
                // Find the selected problem
                const selectedProblem = problems.find(p => p.id === selectedProblemId);
                
                if (selectedProblem) {
                    // Auto-fill reason with problem name (but allow editing)
                    reasonName.value = selectedProblem.name;
                    // Make reason editable
                    reasonName.readOnly = false;
                    reasonName.classList.remove('bg-gray-50');
                }
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Get current system and problem IDs from server
            const currentSystemId = '{{ $currentSystemId ?? old("system_select") }}';
            const currentProblemId = '{{ $currentProblemId ?? old("problem_select") }}';
            
            // Set system select value and trigger change to populate problems
            if (systemSelect && currentSystemId) {
                systemSelect.value = currentSystemId;
                systemSelect.dispatchEvent(new Event('change'));
                
                // Set problem select value after problems are populated
                if (currentProblemId && problemSelect) {
                    setTimeout(() => {
                        problemSelect.value = currentProblemId;
                        // Make reason name editable since problem is selected
                        if (reasonName) {
                            reasonName.readOnly = false;
                            reasonName.classList.remove('bg-gray-50');
                        }
                    }, 100);
                }
            } else {
                // If no system selected, make reason name editable (for existing data)
                if (reasonName && reasonName.value) {
                    reasonName.readOnly = false;
                    reasonName.classList.remove('bg-gray-50');
                }
            }
        });
        </script>
    </div>
</div>
@endsection

