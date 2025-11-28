@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Standard Details</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('standards.edit', $standard->id) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('standards.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                        <p class="text-gray-900 font-semibold">{{ $standard->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Unit</label>
                        <p class="text-gray-900">{{ $standard->unit ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        @if($standard->status == 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Machine Type</label>
                        @if($standard->machineTypes->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($standard->machineTypes as $machineType)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ $machineType->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-900">All Types</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Reference Information -->
            @if($standard->reference_type || $standard->reference_code || $standard->reference_name)
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Reference Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($standard->reference_type)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reference Type</label>
                        <p class="text-gray-900">{{ $standard->reference_type }}</p>
                    </div>
                    @endif
                    
                    @if($standard->reference_code)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reference Code</label>
                        <p class="text-gray-900 font-medium">{{ $standard->reference_code }}</p>
                    </div>
                    @endif
                    
                    @if($standard->reference_name)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reference Name</label>
                        <p class="text-gray-900">{{ $standard->reference_name }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Standard Values -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Standard Values</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Min Value</label>
                        <p class="text-gray-900">{{ $standard->min_value ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Max Value</label>
                        <p class="text-gray-900">{{ $standard->max_value ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Target Value</label>
                        <p class="text-gray-900">{{ $standard->target_value ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Standard Variants -->
            @if($standard->variants->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Standard Variants (Zones/Levels)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Value</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Value</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($standard->variants->sortBy('order') as $variant)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $variant->order }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $variant->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $variant->min_value }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $variant->max_value }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded border" style="background-color: {{ $variant->color ?? '#22C55E' }}"></div>
                                        <span class="text-gray-600">{{ $variant->color ?? '#22C55E' }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            
            <!-- Additional Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
                @if($standard->photos && $standard->photos->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Photos</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($standard->photos as $photo)
                            <div>
                                <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->name ?? $standard->name }}" class="w-full h-48 object-cover rounded border">
                                @if($photo->name)
                                    <p class="text-xs text-gray-600 mt-1 text-center">{{ $photo->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @elseif($standard->photo)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Photo</label>
                    <img src="{{ Storage::url($standard->photo) }}" alt="{{ $standard->name }}" class="w-48 h-48 object-cover rounded border">
                </div>
                @endif
                
                @if($standard->description)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $standard->description }}</p>
                </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Keterangan</label>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $standard->keterangan ?? 'sample' }}</p>
                </div>
            </div>
            
            <!-- Usage Information -->
            @if($standard->predictiveMaintenanceSchedules->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Usage</h3>
                <p class="text-gray-600">This standard is used in <strong>{{ $standard->predictiveMaintenanceSchedules->count() }}</strong> predictive maintenance schedule(s).</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

