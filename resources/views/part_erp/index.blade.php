@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ extractModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Part ERP</h1>
            <div class="flex items-center gap-3">
                @if(auth()->user()->email === 'wahid@tpmcmms.id')
                <button type="button" @click="extractModalOpen = true" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Extract from Downtime
                </button>
                @endif
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('part-erp.download') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Excel
                </a>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload Excel
                </button>
                @endif
                <a href="{{ route('part-erp.stock-movement-report') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Laporan Penggunaan Part
                </a>
                <a href="{{ route('part-erp.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('extraction_details'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <p>Created: {{ session('extraction_details')['created'] }}</p>
                <p>Skipped: {{ session('extraction_details')['skipped'] }}</p>
                @if(!empty(session('extraction_details')['errors']))
                    <details class="mt-2"><summary class="cursor-pointer font-semibold">Errors</summary>
                        <ul class="list-disc list-inside text-sm">@foreach(session('extraction_details')['errors'] as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </details>
                @endif
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Search & Filter -->
        <form method="GET" action="{{ route('part-erp.index') }}" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200" id="filterForm">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Part number / Name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">System</label>
                    <select name="system_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        @foreach($systems ?? collect() as $s)
                            <option value="{{ $s->id ?? '' }}" {{ request('system_id') == ($s->id ?? '') ? 'selected' : '' }}>{{ $s->nama_sistem ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300 text-amber-600">
                    <span class="text-sm text-gray-700">Low stock only</span>
                </label>
                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'part_number') }}" id="sortBy">
                <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}" id="sortDir">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg">Filter</button>
                <a href="{{ route('part-erp.index') }}" class="text-gray-600 hover:text-gray-800 text-sm py-2">Reset</a>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        @php
                            $sortBy = request('sort_by', 'part_number'); $sortDir = request('sort_dir', 'asc');
                            $sortUrl = fn($col) => route('part-erp.index', array_merge(request()->only('search','system_id','low_stock','page'), ['sort_by' => $col, 'sort_dir' => ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc']));
                        @endphp
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"><a href="{{ $sortUrl('part_number') }}" class="hover:underline">Part Number</a> @if($sortBy === 'part_number') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"><a href="{{ $sortUrl('name') }}" class="hover:underline">Name</a> @if($sortBy === 'name') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">System</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Specification</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Brand</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"><a href="{{ $sortUrl('stock') }}" class="hover:underline">Stock</a> @if($sortBy === 'stock') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Min</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"><a href="{{ $sortUrl('price') }}" class="hover:underline">Price</a> @if($sortBy === 'price') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($partErps as $partErp)
                    @php $isLowStock = ($partErp->minimum_stock ?? 0) > 0 && ($partErp->stock ?? 0) < $partErp->minimum_stock; @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors {{ $isLowStock ? 'bg-amber-50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($partErps->currentPage() - 1) * $partErps->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $partErp->part_number }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('part-erp.show', $partErp->id) }}" class="text-blue-600 hover:underline">{{ $partErp->name }}</a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            @php
                                $systemName = '-';
                                if ($partErp->system) {
                                    // If relation exists, use it
                                    $systemName = $partErp->system->nama_sistem;
                                } elseif ($partErp->category) {
                                    // Try to find system by ID if category is numeric
                                    if (is_numeric($partErp->category)) {
                                        $system = \App\Models\System::find($partErp->category);
                                        if ($system) {
                                            $systemName = $system->nama_sistem;
                                        }
                                    } else {
                                        // Try to find system by nama_sistem if category is string
                                        $system = \App\Models\System::where('nama_sistem', $partErp->category)->first();
                                        if ($system) {
                                            $systemName = $system->nama_sistem;
                                        } else {
                                            // If not found, display the category value as is (might be a name)
                                            $systemName = $partErp->category;
                                        }
                                    }
                                }
                            @endphp
                            {{ $systemName }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 300px;">{{ $partErp->description ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $partErp->brand ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $partErp->unit ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            @if($isLowStock)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800" title="Low stock">{{ $partErp->stock ?? 0 }}</span>
                            @else
                                {{ $partErp->stock ?? 0 }}
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $partErp->minimum_stock ?? 0 }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $partErp->price ? number_format($partErp->price, 2) : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            @if($partErp->machineTypes && $partErp->machineTypes->count() > 0)
                                {{ $partErp->machineTypes->pluck('name')->implode(', ') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('part-erp.show', $partErp->id) }}" class="inline-flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('part-erp.edit', ['part_erp' => $partErp->id, 'page' => $partErps->currentPage()]) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('part-erp.destroy', $partErp->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this part ERP?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500">No part ERP found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($partErps->hasPages())
                <div class="mt-4">
                    {{ $partErps->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Extract from Downtime Modal (inside x-data wrapper) -->
@if(auth()->user()->role === 'admin')
<div id="uploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Upload Excel File</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('part-erp.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Excel File (.xlsx, .xls)</label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-2 text-xs text-gray-500">Format Excel: Kolom pertama harus header (part_number, name, description, category, brand, unit, stock, price, location)</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Upload</button>
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-600 hover:text-gray-800">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

    <!-- Extract from Downtime Modal -->
    <div x-show="extractModalOpen" x-cloak
         x-data="{ selectedSource: null, previewData: null, loading: false, previewLoaded: false }"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.self="extractModalOpen = false"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto" @click.stop
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Extract Part ERP from Downtime</h3>
            </div>
            <div class="p-6">
                <div x-show="!previewLoaded" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Data Source</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50"><input type="radio" x-model="selectedSource" value="downtime_erp" class="mr-3" @change="previewLoaded = false; previewData = null"><div><div class="font-medium text-gray-900">Downtime ERP</div><div class="text-sm text-gray-500">Extract from downtime_erp table</div></div></label>
                        <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50"><input type="radio" x-model="selectedSource" value="downtime_erp2" class="mr-3" @change="previewLoaded = false; previewData = null"><div><div class="font-medium text-gray-900">Downtime ERP2</div><div class="text-sm text-gray-500">Extract from downtime_erp2 table</div></div></label>
                    </div>
                    <button type="button" @click="if (!selectedSource) { alert('Please select a data source first'); return; } loading = true; fetch('{{ route('part-erp.preview-from-downtime') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ data_source: selectedSource }) }).then(r => r.json()).then(data => { loading = false; if (data.success) { previewData = data; previewLoaded = true; } else { alert('Preview failed: ' + (data.message || 'Unknown error')); } }).catch(e => { loading = false; alert('Error: ' + e.message); });"
                            :disabled="!selectedSource || loading"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <span x-show="!loading">Preview Data</span><span x-show="loading">Loading...</span>
                    </button>
                </div>
                <template x-if="previewLoaded && previewData">
                    <div class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <h4 class="font-semibold text-blue-900 mb-3">Preview</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded p-3"><div class="text-sm text-gray-600">Total Unique</div><div class="text-2xl font-bold text-gray-900" x-text="previewData?.total_unique || 0"></div></div>
                                <div class="bg-white rounded p-3"><div class="text-sm text-gray-600">New (Will Create)</div><div class="text-2xl font-bold text-green-600" x-text="previewData?.new_count || 0"></div></div>
                                <div class="bg-white rounded p-3"><div class="text-sm text-gray-600">Existing (Skip)</div><div class="text-2xl font-bold text-yellow-600" x-text="previewData?.existing_count || 0"></div></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Sample (first 20)</h4>
                            <div class="max-h-48 overflow-y-auto">
                                <table class="min-w-full text-sm"><thead class="bg-gray-200"><tr><th class="px-3 py-2 text-left">No</th><th class="px-3 py-2 text-left">Part Name</th></tr></thead><tbody>
                                <template x-if="previewData?.sample_data && previewData.sample_data.length > 0">
                                    <template x-for="(item, i) in previewData.sample_data" :key="i">
                                        <tr class="border-b"><td class="px-3 py-2" x-text="i + 1"></td><td class="px-3 py-2" x-text="item?.name || ''"></td></tr>
                                    </template>
                                </template>
                                <template x-if="!previewData?.sample_data || previewData.sample_data.length === 0">
                                    <tr><td colspan="2" class="px-3 py-2 text-center text-gray-500">No sample data</td></tr>
                                </template>
                                </tbody></table>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('part-erp.extract-from-downtime') }}">
                            @csrf
                            <input type="hidden" name="data_source" :value="selectedSource">
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="previewLoaded = false; previewData = null; selectedSource = null" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">Back</button>
                                <button type="button" @click="extractModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Proses</button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

