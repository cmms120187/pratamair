@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">System Details</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('systems.edit', $system->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('systems.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID</label>
                    <p class="text-gray-900">{{ $system->id }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sistem</label>
                    <p class="text-gray-900 text-lg font-semibold">{{ $system->nama_sistem }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <p class="text-gray-900">{{ $system->deskripsi ?? '-' }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                        <p class="text-gray-900">{{ $system->created_at ? $system->created_at->format('Y-m-d H:i:s') : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                        <p class="text-gray-900">{{ $system->updated_at ? $system->updated_at->format('Y-m-d H:i:s') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




