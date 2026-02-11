@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ extractModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Lines</h1>
            <div class="flex items-center gap-3">
                @if(auth()->user()->email === 'wahid@tpmcmms.id')
                <button type="button" @click="extractModalOpen = true" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Extract from Downtime
                </button>
                @endif
                <form action="{{ route('lines.import-from-room-erp') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Import lines dari tabel room_erp? Ini akan membuat lines baru dari line_name yang ada.')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Import dari Room ERP
                    </button>
                </form>
                <a href="{{ route('lines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
                @if(session('extraction_details'))
                    <div class="mt-2 text-sm">
                        <p>Created: {{ session('extraction_details')['created'] }}</p>
                        <p>Skipped: {{ session('extraction_details')['skipped'] }}</p>
                        @if(!empty(session('extraction_details')['errors']))
                            <details class="mt-2">
                                <summary class="cursor-pointer font-semibold">Errors ({{ count(session('extraction_details')['errors']) }})</summary>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach(session('extraction_details')['errors'] as $error)
                                        <li class="text-xs">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                @endif
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Process</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($lines as $line)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($lines->currentPage() - 1) * $lines->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $line->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $line->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $line->plant ? $line->plant->name : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $line->process ? $line->process->name : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('lines.edit', $line->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('lines.destroy', $line->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this line?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No lines found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($lines->hasPages())
                <div class="mt-4">
                    {{ $lines->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Extract Modal -->
    <div x-show="extractModalOpen" 
         x-cloak
         x-data="{ 
             selectedSource: null, 
             previewData: null, 
             loading: false,
             previewLoaded: false 
         }"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.self="extractModalOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Extract Lines from Downtime</h3>
            </div>
            <div class="p-6">
                <!-- Step 1: Select Data Source -->
                <div x-show="!previewLoaded" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Data Source</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" x-model="selectedSource" value="downtime_erp" class="mr-3" @change="previewLoaded = false; previewData = null">
                            <div>
                                <div class="font-medium text-gray-900">Downtime ERP</div>
                                <div class="text-sm text-gray-500">Extract from downtime_erp table</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" x-model="selectedSource" value="downtime_erp2" class="mr-3" @change="previewLoaded = false; previewData = null">
                            <div>
                                <div class="font-medium text-gray-900">Downtime ERP2</div>
                                <div class="text-sm text-gray-500">Extract from downtime_erp2 table</div>
                            </div>
                        </label>
                    </div>
                    <button 
                        type="button" 
                        @click="
                            if (!selectedSource) { alert('Please select a data source first'); return; }
                            loading = true;
                            fetch('{{ route('lines.preview-from-downtime') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ data_source: selectedSource })
                            })
                            .then(response => response.json())
                            .then(data => {
                                loading = false;
                                if (data.success) {
                                    previewData = data;
                                    previewLoaded = true;
                                } else {
                                    alert('Preview failed: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                loading = false;
                                alert('Error: ' + error.message);
                            });
                        "
                        :disabled="!selectedSource || loading"
                        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <span x-show="!loading">Preview Data</span>
                        <span x-show="loading">Loading...</span>
                    </button>
                </div>

                <!-- Step 2: Preview Data -->
                <template x-if="previewLoaded && previewData">
                    <div class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <h4 class="font-semibold text-blue-900 mb-3">Preview Data</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded p-3">
                                    <div class="text-sm text-gray-600">Total Unique</div>
                                    <div class="text-2xl font-bold text-gray-900" x-text="previewData?.total_unique || 0"></div>
                                </div>
                                <div class="bg-white rounded p-3">
                                    <div class="text-sm text-gray-600">New (Will be Created)</div>
                                    <div class="text-2xl font-bold text-green-600" x-text="previewData?.new_count || 0"></div>
                                </div>
                                <div class="bg-white rounded p-3">
                                    <div class="text-sm text-gray-600">Existing (Will be Skipped)</div>
                                    <div class="text-2xl font-bold text-yellow-600" x-text="previewData?.existing_count || 0"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Sample Data (showing first 20)</h4>
                            <div class="max-h-64 overflow-y-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="px-3 py-2 text-left">No</th>
                                            <th class="px-3 py-2 text-left">Line</th>
                                            <th class="px-3 py-2 text-left">Plant</th>
                                            <th class="px-3 py-2 text-left">Process</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="previewData?.sample_data && previewData.sample_data.length > 0">
                                            <template x-for="(item, index) in previewData.sample_data" :key="index">
                                                <tr class="border-b">
                                                    <td class="px-3 py-2" x-text="index + 1"></td>
                                                    <td class="px-3 py-2" x-text="item.line || ''"></td>
                                                    <td class="px-3 py-2" x-text="item.plant || ''"></td>
                                                    <td class="px-3 py-2" x-text="item.process || '-'"></td>
                                                </tr>
                                            </template>
                                        </template>
                                        <template x-if="!previewData?.sample_data || previewData.sample_data.length === 0">
                                            <tr>
                                                <td colspan="4" class="px-3 py-2 text-center text-gray-500">No sample data available</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('lines.extract-from-downtime') }}" id="extractForm">
                            @csrf
                            <input type="hidden" name="data_source" :value="selectedSource">
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="previewLoaded = false; previewData = null; selectedSource = null" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition">
                                    Back
                                </button>
                                <button type="button" @click="extractModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                    Proses
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
