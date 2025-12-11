@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Machine ERP Details</h1>
                <p class="text-sm text-gray-600">View complete information about the machine</p>
            </div>
            <a href="{{ route('machine-erp.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header Section -->
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">{{ $machineErp->idMachine }}</h2>
                <p class="text-blue-100 text-sm mt-1">{{ $machineErp->type_name ?? 'No Type' }}</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Basic Information -->
                    <div class="space-y-6">
                        <!-- Machine Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Information</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">ID Machine:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $machineErp->idMachine }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Type Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->type_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Brand Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->brand_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Model Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->model_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Serial Number:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->serial_number ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Tahun Production:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->tahun_production ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">No Document:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->no_document ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Location Information</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Plant Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->plant_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Process Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->process_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Line Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->line_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Room Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->room_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Machine Type Information -->
                        @if($machineErp->machineType)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Type Details</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $machineErp->machineType->name }}</span>
                                </div>
                                @if($machineErp->machineType->brand)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Brand:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->machineType->brand }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->model)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Model:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->machineType->model }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->description)
                                <div class="flex flex-col">
                                    <span class="w-full text-sm font-medium text-gray-600 mb-1">Description:</span>
                                    <span class="w-full text-sm text-gray-900">{{ $machineErp->machineType->description }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->groupRelation)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Group:</span>
                                    <span class="w-2/3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $machineErp->machineType->groupRelation->name }}
                                        </span>
                                    </span>
                                </div>
                                @endif
                                @if($machineErp->machineType->systems->isNotEmpty())
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Systems:</span>
                                    <span class="w-2/3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($machineErp->machineType->systems as $system)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $system->nama_sistem }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </span>
                                </div>
                                @endif
                                @if($machineErp->machineType->photo)
                                <div class="flex flex-col">
                                    <span class="w-full text-sm font-medium text-gray-600 mb-2">Machine Type Photo:</span>
                                    @php
                                        $mtPhotoUrl = asset('storage/' . $machineErp->machineType->photo);
                                        $mtPhotoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($machineErp->machineType->photo);
                                    @endphp
                                    @if($mtPhotoExists)
                                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 inline-block">
                                            <img src="{{ $mtPhotoUrl }}" 
                                                 alt="Machine Type Photo" 
                                                 class="max-w-xs max-h-48 object-contain rounded border shadow-sm"
                                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display:none;" class="text-sm text-red-500">
                                                Photo tidak dapat dimuat
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-red-500">
                                            Photo tidak ditemukan: {{ $machineErp->machineType->photo }}
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Photo -->
                    <div class="space-y-6">
                        @php
                            $actualPhoto = $machineErp->attributes['photo'] ?? null;
                            $displayPhoto = null;
                            $photoSource = '';
                            
                            // Priority: machine_erp photo > model photo > machine_type photo
                            if ($actualPhoto) {
                                $displayPhoto = $actualPhoto;
                                $photoSource = 'Machine ERP';
                            } elseif ($modelPhoto) {
                                $displayPhoto = $modelPhoto;
                                $photoSource = 'Model (' . $machineErp->model_name . ')';
                            } elseif ($machineErp->machineType && $machineErp->machineType->photo) {
                                $displayPhoto = $machineErp->machineType->photo;
                                $photoSource = 'Machine Type';
                            }
                        @endphp

                        @if($displayPhoto)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Photo <span class="text-sm font-normal text-gray-500">(from {{ $photoSource }})</span></h3>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                @php
                                    $photoUrl = asset('storage/' . $displayPhoto);
                                    $photoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($displayPhoto);
                                @endphp
                                @if($photoExists)
                                    <img src="{{ $photoUrl }}" 
                                         alt="Machine Photo" 
                                         class="w-full h-auto rounded-lg shadow-md object-cover"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display:none;" class="text-sm text-red-500">
                                        Photo tidak dapat dimuat: {{ $displayPhoto }}
                                    </div>
                                @else
                                    <div class="text-sm text-red-500">
                                        Photo tidak ditemukan: {{ $displayPhoto }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Photo</h3>
                            <div class="bg-gray-50 rounded-lg p-8 border border-gray-200 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm">No photo available</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 pt-6 border-t border-gray-200 flex items-center gap-3">
                    <a href="{{ route('machine-erp.edit', $machineErp->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('machine-erp.destroy', $machineErp->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Delete this machine ERP?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

