@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create System</h1>
                <p class="text-sm text-gray-500 mt-1">Add a new system to the machinery system</p>
            </div>
            <a href="{{ route('systems.index') }}" class="text-gray-600 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

        <form action="{{ route('systems.store') }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">System Information</h2>
                
                <div class="mb-4">
                    <label for="nama_sistem" class="block text-sm font-medium text-gray-700 mb-2">
                        System Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama_sistem" 
                        id="nama_sistem" 
                        value="{{ old('nama_sistem') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nama_sistem') border-red-500 @enderror"
                        placeholder="Enter system name (e.g., Mechanical, Electrical, Hydraulic)"
                        required
                        autofocus
                    >
                    @error('nama_sistem')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Enter a unique system name. This will be used to categorize maintenance points and problems.</p>
                </div>
                
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        rows="4"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('deskripsi') border-red-500 @enderror"
                        placeholder="Enter system description (optional)"
                    >{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Provide a brief description of this system and its purpose.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a 
                    href="{{ route('systems.index') }}" 
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg shadow transition duration-150 ease-in-out flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition duration-150 ease-in-out flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create System
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
