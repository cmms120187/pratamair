@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Standards Management</h1>
            <a href="{{ route('standards.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Standard
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <style>
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .table-row {
                    animation: fadeInUp 0.3s ease-out;
                    animation-fill-mode: both;
                }
                .table-row:nth-child(1) { animation-delay: 0.05s; }
                .table-row:nth-child(2) { animation-delay: 0.1s; }
                .table-row:nth-child(3) { animation-delay: 0.15s; }
                .table-row:nth-child(4) { animation-delay: 0.2s; }
                .table-row:nth-child(5) { animation-delay: 0.25s; }
                .table-row:nth-child(6) { animation-delay: 0.3s; }
                .table-row:nth-child(7) { animation-delay: 0.35s; }
                .table-row:nth-child(8) { animation-delay: 0.4s; }
                .table-row:nth-child(9) { animation-delay: 0.45s; }
                .table-row:nth-child(10) { animation-delay: 0.5s; }
            </style>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-stone-500 via-stone-600 to-neutral-600 shadow-md">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Photo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Unit</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Min - Max</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Target</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($standards as $standard)
                        <tr class="table-row {{ $loop->even ? 'bg-gradient-to-r from-stone-50 to-neutral-50' : 'bg-white' }} hover:bg-gradient-to-r hover:from-stone-100 hover:to-neutral-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.01]">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $loop->iteration + ($standards->currentPage() - 1) * $standards->perPage() }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $displayPhoto = null;
                                    $photoUrl = null;
                                    
                                    // Prioritaskan photo dari relasi photos
                                    if ($standard->photos && $standard->photos->count() > 0) {
                                        $displayPhoto = $standard->photos->first()->photo_path;
                                    } elseif ($standard->photo) {
                                        // Fallback ke photo legacy
                                        $displayPhoto = $standard->photo;
                                    }
                                    
                                    // Generate URL jika ada photo
                                    if ($displayPhoto) {
                                        // Cek apakah file dengan path asli ada
                                        $actualPath = $displayPhoto;
                                        
                                        // Jika file tidak ada, coba cari dengan ekstensi .webp
                                        if (strpos($displayPhoto, 'images/') !== 0) {
                                            // Hanya cek untuk file di storage, bukan di public/images
                                            if (!Storage::disk('public')->exists($displayPhoto)) {
                                                $pathInfo = pathinfo($displayPhoto);
                                                $webpPath = ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $pathInfo['filename'] . '.webp';
                                                if (Storage::disk('public')->exists($webpPath)) {
                                                    $actualPath = $webpPath;
                                                }
                                            }
                                        }
                                        
                                        if (strpos($actualPath, 'images/') === 0) {
                                            // Old format: images/ISO 10816.jpg -> use asset() for public folder
                                            $photoUrl = asset($actualPath);
                                        } elseif (strpos($actualPath, 'standards/') === 0 || strpos($actualPath, 'maintenance-points/') === 0) {
                                            // New format: standards/xxx.jpg or maintenance-points/xxx.jpg
                                            // File sudah di public/public-storage, jadi gunakan asset() dengan path public-storage
                                            $photoUrl = asset('public-storage/' . $actualPath);
                                        } else {
                                            // Default: assume it's in public disk storage
                                            $photoUrl = asset('public-storage/' . $actualPath);
                                        }
                                    }
                                @endphp
                                
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $standard->name }}" class="w-16 h-16 object-cover rounded border" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; console.error('Failed to load image: {{ $photoUrl }}');">
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ $standard->name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($standard->reference_code)
                                    <div class="font-medium">{{ $standard->reference_code }}</div>
                                    @if($standard->reference_name)
                                        <div class="text-xs text-gray-400">{{ $standard->reference_name }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $standard->unit ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($standard->min_value !== null || $standard->max_value !== null)
                                    {{ $standard->min_value ?? '?' }} - {{ $standard->max_value ?? '?' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $standard->target_value ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($standard->machineTypes->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($standard->machineTypes as $machineType)
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $machineType->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">All Types</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($standard->status == 'active')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('standards.show', $standard->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('standards.edit', $standard->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('standards.destroy', $standard->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus standard ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                No standards found. <a href="{{ route('standards.create') }}" class="text-blue-600 hover:underline">Create one</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($standards->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $standards->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

