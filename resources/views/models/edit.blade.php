@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Model</h1>
                <p class="text-sm text-gray-500 mt-1">Update model information</p>
            </div>
            <a href="{{ route('models.index') }}" class="text-gray-600 hover:text-gray-800 transition">
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

        <form action="{{ route('models.update', $model->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Model Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Model Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $model->name) }}" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                            placeholder="Enter model name"
                            required
                            autofocus
                        >
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Machine Type <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="type_id" 
                            id="type_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('type_id') border-red-500 @enderror" 
                            required
                        >
                            <option value="">Select Machine Type</option>
                            @foreach($machineTypes as $machineType)
                                <option value="{{ $machineType->id }}" {{ old('type_id', $model->type_id) == $machineType->id ? 'selected' : '' }}>
                                    {{ $machineType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Brand <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="brand_id" 
                            id="brand_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('brand_id') border-red-500 @enderror" 
                            required
                        >
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $model->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                    @php
                        $currentPhotoUrl = null;
                        if ($model->photo_id && $model->photoModel) {
                            $currentPhotoUrl = route('photos.show', $model->photo_id);
                        } elseif ($model->photo) {
                            $photo = \App\Models\Photo::where('file_path', $model->photo)
                                ->orWhere('file_path', 'like', '%' . basename($model->photo))
                                ->first();
                            if ($photo) {
                                $currentPhotoUrl = route('photos.show', $photo->id);
                            } else {
                                $currentPhotoUrl = asset('public-storage/' . $model->photo);
                            }
                        }
                    @endphp
                    @if($currentPhotoUrl)
                        <div class="mb-3">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Current Photo:</p>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 inline-block">
                                <img src="{{ $currentPhotoUrl }}" alt="Current Photo" class="max-w-xs max-h-64 object-contain rounded border shadow-sm" onerror="this.style.display='none';">
                            </div>
                        </div>
                    @endif
                    <input 
                        type="file" 
                        name="photo" 
                        id="photo" 
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('photo') border-red-500 @enderror"
                        onchange="previewPhoto(event)"
                    >
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF, WebP (Max: 5MB). Kosongkan jika tidak ingin mengubah photo.</p>
                    <div id="photo_preview" class="hidden mt-3">
                        <p class="text-sm font-semibold text-gray-700 mb-2">New Photo Preview:</p>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 inline-block">
                            <img id="photo_preview_img" src="" alt="Preview" class="max-w-xs max-h-64 object-contain rounded border shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Model Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Model ID:</span>
                            <span class="font-semibold text-gray-900 ml-2">{{ $model->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Created At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $model->created_at ? $model->created_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Updated At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $model->updated_at ? $model->updated_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a 
                    href="{{ route('models.index') }}" 
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
                    Update Model
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('photo_preview');
    const previewImg = document.getElementById('photo_preview_img');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}
</script>
@endsection
