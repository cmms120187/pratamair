@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Brand</h1>
                <p class="text-sm text-gray-500 mt-1">Update brand information</p>
            </div>
            <a href="{{ route('brands.index') }}" class="text-gray-600 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('brands.update', $brand->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Brand Information</h2>
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Brand Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name', $brand->name) }}" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                        placeholder="Enter brand name"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Enter the brand name (e.g., SKF, Bosch, Siemens, etc.)</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Brand Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Brand ID:</span>
                            <span class="font-semibold text-gray-900 ml-2">{{ $brand->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Created At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $brand->created_at ? $brand->created_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Updated At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $brand->updated_at ? $brand->updated_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a 
                    href="{{ route('brands.index') }}" 
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
                    Update Brand
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
