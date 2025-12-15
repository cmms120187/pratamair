@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Action</h1>
            <a href="{{ route('actions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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

        <form action="{{ route('actions.update', $action->id) }}" method="POST">
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
                    <label for="reason_select" class="block text-sm font-medium text-gray-700 mb-2">Reason <span class="text-red-500">*</span></label>
                    <select name="reason_select" id="reason_select" required disabled class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed @error('reason_select') border-red-500 @enderror">
                        <option value="">-- Pilih Reason --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Reason terkunci (tidak dapat diubah saat edit)</p>
                    @error('reason_select')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Action Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $action->name) }}" placeholder="e.g., GANTI SPARE PART" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                    <p class="text-xs text-gray-500 mt-1">Action name dapat diedit</p>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end gap-4">
                    <a href="{{ route('actions.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded shadow transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow transition">
                        Update Action
                    </button>
                </div>
            </div>
        </form>
        
        <script>
        // System, Problem, and Reason data from server (already mapped in controller)
        const systems = @json($systems ?? []);
        const problems = @json($problems ?? []);
        const reasons = @json($reasons ?? []);
        
        // System, Problem, and Reason filtering functionality
        const systemSelect = document.getElementById('system_select');
        const problemSelect = document.getElementById('problem_select');
        const reasonSelect = document.getElementById('reason_select');
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Get current system, problem, and reason IDs from server
            const currentSystemId = '{{ $currentSystemId ?? old("system_select") }}';
            const currentProblemId = '{{ $currentProblemId ?? old("problem_select") }}';
            const currentReasonId = '{{ $currentReasonId ?? old("reason_select") }}';
            
            // Set system select value and trigger change to populate problems
            if (systemSelect && currentSystemId) {
                systemSelect.value = currentSystemId;
                
                // Filter problems that belong to the selected system
                const filteredProblems = problems.filter(problem => {
                    return problem.system_ids && problem.system_ids.includes(currentSystemId);
                });
                
                // Populate problem dropdown
                filteredProblems.forEach(problem => {
                    const option = document.createElement('option');
                    option.value = problem.id;
                    option.textContent = problem.name + (problem.problem_header ? ' (' + problem.problem_header + ')' : '');
                    option.setAttribute('data-problem-name', problem.name);
                    problemSelect.appendChild(option);
                });
                
                // Set problem select value
                if (currentProblemId && problemSelect) {
                    problemSelect.value = currentProblemId;
                    
                    // Filter reasons by selected problem_id
                    const filteredReasons = reasons.filter(reason => {
                        return reason.problem_id && reason.problem_id === currentProblemId;
                    });
                    
                    // Populate reason dropdown
                    filteredReasons.forEach(reason => {
                        const option = document.createElement('option');
                        option.value = reason.id;
                        option.textContent = reason.name;
                        option.setAttribute('data-reason-name', reason.name);
                        reasonSelect.appendChild(option);
                    });
                    
                    // Set reason select value
                    if (currentReasonId && reasonSelect) {
                        reasonSelect.value = currentReasonId;
                    }
                }
            }
        });
        </script>
    </div>
</div>
@endsection

