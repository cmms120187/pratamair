@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Part ERP</h1>
            <p class="text-sm text-gray-600">Update part ERP information</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('part-erp.update', $partErp->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="page" value="{{ old('page', $page ?? 1) }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="part_number" class="block text-sm font-semibold text-gray-700 mb-2">Part Number <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="part_number" 
                           id="part_number" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('part_number') border-red-500 @enderror" 
                           value="{{ old('part_number', $partErp->part_number) }}" 
                           required
                           placeholder="Enter part number">
                    @error('part_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                           value="{{ old('name', $partErp->name) }}" 
                           required
                           placeholder="Enter part name">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('description') border-red-500 @enderror" 
                          placeholder="Enter description">{{ old('description', $partErp->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="system_id" class="block text-sm font-semibold text-gray-700 mb-2">Category (System)</label>
                    <select name="system_id" 
                            id="system_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('system_id') border-red-500 @enderror">
                        <option value="">Pilih Category (System)</option>
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}" {{ old('system_id', $partErp->system && $partErp->system->id == $system->id ? $system->id : '') == $system->id ? 'selected' : '' }}>
                                {{ $system->nama_sistem }}
                            </option>
                        @endforeach
                    </select>
                    @error('system_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">Brand</label>
                    <input type="text" 
                           name="brand" 
                           id="brand" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('brand') border-red-500 @enderror" 
                           value="{{ old('brand', $partErp->brand) }}" 
                           placeholder="Enter brand">
                    @error('brand')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit" class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                    <input type="text" 
                           name="unit" 
                           id="unit" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('unit') border-red-500 @enderror" 
                           value="{{ old('unit', $partErp->unit) }}" 
                           placeholder="Enter unit">
                    @error('unit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">Stock</label>
                    <input type="number" 
                           name="stock" 
                           id="stock" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('stock') border-red-500 @enderror" 
                           value="{{ old('stock', $partErp->stock) }}" 
                           placeholder="Enter stock">
                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           step="0.01" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('price') border-red-500 @enderror" 
                           value="{{ old('price', $partErp->price) }}" 
                           placeholder="Enter price">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Machine Type (Location) <span class="text-gray-500 text-xs">(Pilih satu atau lebih)</span></label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4 border border-gray-300 rounded-lg bg-gray-50 max-h-64 overflow-y-auto">
                    @foreach($machineTypes as $machineType)
                        <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 p-2 rounded">
                            <input type="checkbox" 
                                   name="machine_type_ids[]" 
                                   value="{{ $machineType->id }}" 
                                   {{ in_array($machineType->id, old('machine_type_ids', $partErp->machineTypes->pluck('id')->toArray())) ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ $machineType->name }}</span>
                        </label>
                    @endforeach
                </div>
                @if($machineTypes->isEmpty())
                    <p class="text-sm text-gray-500 mt-2">No machine types available. Please create machine types first.</p>
                @endif
                @error('machine_type_ids')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('machine_type_ids.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Part ERP
                </button>
                <a href="{{ route('part-erp.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

