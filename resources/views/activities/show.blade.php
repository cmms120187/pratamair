@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Activity Details</h1>
                <p class="text-sm text-gray-600">View complete information about the activity</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('activities.edit', $activity->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('activities.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header Section -->
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Activity #{{ $activity->id }}</h2>
                <p class="text-blue-100 text-sm mt-1">{{ $activity->date }} - {{ $activity->start }} to {{ $activity->stop }}</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Information (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Date:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $activity->date }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Start Time:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->start ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Stop Time:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->stop ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Duration:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->duration ? $activity->duration . ' minutes' : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Location Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Plant:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->plant ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Process:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->process ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Line:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->line ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Room Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->room_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Description</h3>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $activity->description ?? '-' }}</p>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Remarks</h3>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $activity->remarks ?? '-' }}</p>
                        </div>

                        <!-- Personnel Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Personnel Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">ID Mekanik:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $activity->id_mekanik ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Nama Mekanik:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $activity->nama_mekanik ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Machine Information -->
                        @if($activity->id_mesin)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Information</h3>
                            <div class="flex">
                                <span class="w-1/3 text-sm font-medium text-gray-600">ID Mesin:</span>
                                <span class="w-2/3 text-sm text-gray-900">{{ $activity->id_mesin }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Photos (1/3 width) -->
                    <div class="lg:col-span-1">
                        @if($activity->photos && count($activity->photos) > 0)
                        <div class="sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Photos</h3>
                            <div class="space-y-4">
                                @foreach($activity->photos as $photo)
                                    <div class="relative">
                                        <img src="{{ Storage::url($photo) }}" alt="Activity Photo" class="w-full h-auto object-cover rounded-lg border shadow">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Photos</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-500">No photos available</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

