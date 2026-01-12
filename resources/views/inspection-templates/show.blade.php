@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Detail Template Inspeksi</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('inspection-templates.edit', $template->id) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('inspection-templates.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Template Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Template</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nama Template</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $template->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Machine Type</label>
                    <p class="text-sm text-gray-900">{{ $template->machineType->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <p class="text-sm">
                        @if($template->status == 'active')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">Inactive</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Oleh</label>
                    <p class="text-sm text-gray-900">{{ $template->createdBy->name ?? '-' }}</p>
                </div>
                @if($template->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi</label>
                    <p class="text-sm text-gray-900">{{ $template->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Parameters -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Parameter Template</h2>
            @if($template->parameters->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Parameter</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Range</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Urutan</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Foto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instruksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($template->parameters->sortBy('sequence') as $index => $param)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $param->parameter_name }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $param->unit ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">
                                @if($param->min_value !== null || $param->max_value !== null)
                                    {{ $param->min_value !== null ? number_format($param->min_value, 4) : '-' }} - 
                                    {{ $param->max_value !== null ? number_format($param->max_value, 4) : '-' }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $param->sequence ?? $index }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                @if($param->photo)
                                    <img src="{{ asset('storage/' . $param->photo) }}" alt="Photo" class="w-16 h-16 object-cover rounded border mx-auto cursor-pointer" onclick="window.open('{{ asset('storage/' . $param->photo) }}', '_blank')">
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $param->instruction ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-4">Tidak ada parameter dalam template ini.</p>
            @endif
        </div>
    </div>
</div>
@endsection
