@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Sparepart</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('parts.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Sparepart Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="part_number" class="block text-sm font-medium text-gray-700 mb-2">Part Number</label>
                        <input type="text" name="part_number" id="part_number" value="{{ old('part_number') }}" class="w-full border rounded px-3 py-2 @error('part_number') border-red-500 @enderror" placeholder="e.g., SPR-001">
                        @error('part_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="w-full border rounded px-3 py-2 @error('brand') border-red-500 @enderror" placeholder="e.g., SKF, Bosch, etc">
                        @error('brand')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                        <select name="unit" id="unit" class="w-full border rounded px-3 py-2 @error('unit') border-red-500 @enderror">
                            <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>PCS (Pieces)</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>KG (Kilogram)</option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                            <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                            <option value="roll" {{ old('unit') == 'roll' ? 'selected' : '' }}>Roll</option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                        </select>
                        @error('unit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0" class="w-full border rounded px-3 py-2 @error('stock') border-red-500 @enderror">
                        @error('stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (Rp)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" step="0.01" class="w-full border rounded px-3 py-2 @error('price') border-red-500 @enderror" placeholder="0.00">
                        @error('price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" class="w-full border rounded px-3 py-2 @error('location') border-red-500 @enderror" placeholder="e.g., Warehouse A, Shelf B-3">
                        @error('location')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Systems <span class="text-gray-500 text-xs">(Select one or more)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4 border rounded bg-gray-50">
                        @foreach($systems as $system)
                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 p-2 rounded">
                                <input type="checkbox" name="systems[]" value="{{ $system->id }}" {{ in_array($system->id, old('systems', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror" placeholder="Enter description...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Create Sparepart</button>
                <a href="{{ route('parts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
