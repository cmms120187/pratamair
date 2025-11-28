@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Room ERP</h1>
            <p class="text-sm text-gray-600">Add new room ERP entry (all fields are manual input)</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('room-erp.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="kode_room" class="block text-sm font-semibold text-gray-700 mb-2">Kode Room</label>
                <input type="text" 
                       name="kode_room" 
                       id="kode_room" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('kode_room') border-red-500 @enderror" 
                       value="{{ old('kode_room') }}" 
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
                       value="{{ old('name') }}" 
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
                        <option value="Production" {{ old('category') == 'Production' ? 'selected' : '' }}>Production</option>
                        <option value="Supporting" {{ old('category') == 'Supporting' ? 'selected' : '' }}>Supporting</option>
                        <option value="Warehouse" {{ old('category') == 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
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
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant_name') border-red-500 @enderror" 
                           value="{{ old('plant_name') }}" 
                           placeholder="Enter plant name">
                    @error('plant_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="line_name" class="block text-sm font-semibold text-gray-700 mb-2">Line Name</label>
                    <input type="text" 
                           name="line_name" 
                           id="line_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line_name') border-red-500 @enderror" 
                           value="{{ old('line_name') }}" 
                           placeholder="Enter line name">
                    @error('line_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process Name</label>
                    <input type="text" 
                           name="process_name" 
                           id="process_name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process_name') border-red-500 @enderror" 
                           value="{{ old('process_name') }}" 
                           placeholder="Enter process name">
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
                          placeholder="Enter description">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Room ERP
                </button>
                <a href="{{ route('room-erp.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

