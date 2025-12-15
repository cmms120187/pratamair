@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold mb-6">Create Model</h1>
        <form action="{{ route('models.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label for="name" class="block font-semibold mb-2">Model Machine <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" 
                               placeholder="Masukkan Model Machine"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="type_id" class="block font-semibold mb-2">Type Machine <span class="text-red-500">*</span></label>
                        <select name="type_id" 
                                id="type_id" 
                                class="w-full border rounded px-3 py-2 @error('type_id') border-red-500 @enderror" 
                                required>
                            <option value="">Select Type Machine</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ old('type_id') == $machineType->id ? 'selected' : '' }}>{{ $machineType->name }}</option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="brand_id" class="block font-semibold mb-2">Brand Machine <span class="text-red-500">*</span></label>
                        <select name="brand_id" 
                                id="brand_id" 
                                class="w-full border rounded px-3 py-2 @error('brand_id') border-red-500 @enderror" 
                                required>
                            <option value="">Select Brand Machine</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label for="photo" class="block font-semibold mb-2">Photo (untuk Model ini)</label>
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/*"
                           class="w-full border rounded px-3 py-2 @error('photo') border-red-500 @enderror">
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 5MB). Photo ini akan digunakan untuk semua machine dengan type dan model yang sama.</p>
                    <div id="photo_preview" class="hidden mt-2">
                        <img id="photo_preview_img" src="" alt="Preview" class="max-w-xs max-h-48 object-cover rounded border">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('models.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">
                    Create Model
                </button>
            </div>
        </form>
    </div>
</div>
<script>
// Photo preview
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo_preview');
    const photoPreviewImg = document.getElementById('photo_preview_img');
    
    if (photoInput && photoPreview && photoPreviewImg) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreviewImg.src = e.target.result;
                    photoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
