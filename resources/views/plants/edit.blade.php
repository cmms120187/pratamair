@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Plant</h1>
            <p class="text-sm text-gray-600">Update plant information</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('plants.update', $plant->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Plant Name <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                       value="{{ old('name', $plant->name) }}" 
                       required
                       placeholder="Enter plant name">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Plant
                </button>
                <a href="{{ route('plants.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

