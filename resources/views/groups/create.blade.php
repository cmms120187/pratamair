@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Group</h1>
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('groups.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Name Group <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                       value="{{ old('name') }}" 
                       required
                       placeholder="e.g., Compressing">
            </div>

            <div class="mb-6">
                <label for="systems" class="block text-sm font-semibold text-gray-700 mb-2">
                    Systems
                </label>
                <select name="systems[]" 
                        id="systems" 
                        multiple 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition min-h-[150px]"
                        size="8">
                    @foreach($systems as $system)
                        <option value="{{ $system->id }}" 
                                {{ in_array($system->id, old('systems', [])) ? 'selected' : '' }}>
                            {{ $system->nama_sistem }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">
                    <strong>Tip:</strong> Hold Ctrl (Windows) or Cmd (Mac) to select multiple systems
                </p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('groups.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
