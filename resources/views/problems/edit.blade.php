@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Problem</h1>
            <a href="{{ route('problems.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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

        <form action="{{ route('problems.update', $problem->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="problem_header" class="block text-sm font-medium text-gray-700 mb-2">Problem Header</label>
                        <input type="text" name="problem_header" id="problem_header" value="{{ old('problem_header', $problem->problem_header) }}" placeholder="e.g., ELECTRICAL, MOTOR, MECHANICAL" class="w-full border rounded px-3 py-2 @error('problem_header') border-red-500 @enderror">
                        @error('problem_header')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="systems" class="block text-sm font-medium text-gray-700 mb-2">Systems <span class="text-gray-500 text-xs">(Select one or more)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4 border rounded bg-gray-50">
                        @foreach($systems as $system)
                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 p-2 rounded">
                                <input type="checkbox" name="systems[]" value="{{ $system->id }}" {{ in_array($system->id, old('systems', $problem->systems->pluck('id')->toArray())) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ $system->nama_sistem }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($systems->isEmpty())
                        <p class="text-sm text-gray-500 mt-2">No systems available. Please create systems first.</p>
                    @endif
                    @error('systems')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('systems.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Problem Detail (Name) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $problem->name) }}" placeholder="e.g., MESIN TIDAK DAPAT BEROPERASI" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Must be unique - no duplicates allowed</p>
                </div>
                
                <div>
                    <label for="problem_mm" class="block text-sm font-medium text-gray-700 mb-2">Problem MM</label>
                    <select name="problem_mm" id="problem_mm" class="w-full border rounded px-3 py-2 @error('problem_mm') border-red-500 @enderror">
                        <option value="">Select Problem MM</option>
                        <option value="OTHER" {{ old('problem_mm', $problem->problem_mm) == 'OTHER' ? 'selected' : '' }}>OTHER</option>
                        <option value="MACHINE BREAKDOWN" {{ old('problem_mm', $problem->problem_mm) == 'MACHINE BREAKDOWN' ? 'selected' : '' }}>MACHINE BREAKDOWN</option>
                        <option value="CONVEYOR BELT CHANGEOVER" {{ old('problem_mm', $problem->problem_mm) == 'CONVEYOR BELT CHANGEOVER' ? 'selected' : '' }}>CONVEYOR BELT CHANGEOVER</option>
                        <option value="POWER SUPPLY PROBLEM" {{ old('problem_mm', $problem->problem_mm) == 'POWER SUPPLY PROBLEM' ? 'selected' : '' }}>POWER SUPPLY PROBLEM</option>
                    </select>
                    @error('problem_mm')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end gap-4">
                    <a href="{{ route('problems.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded shadow transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow transition">
                        Update Problem
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

