@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ showFilter: {{ request()->hasAny(['filter_brand', 'filter_system']) ? 'true' : 'false' }} }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Spareparts</h1>
            <a href="{{ route('parts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Create
            </a>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" id="search" placeholder="Search by name or part number..." value="{{ request('search') }}" class="w-full border rounded px-3 py-2">
                </div>
                <button type="button" @click="showFilter = !showFilter" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
            </div>

            <!-- Filter Modal -->
            <div x-show="showFilter" x-cloak class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                <form method="GET" action="{{ route('parts.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <div>
                        <label for="filter_brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <select name="filter_brand" id="filter_brand" class="w-full border rounded px-3 py-2">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" {{ request('filter_brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_system" class="block text-sm font-medium text-gray-700 mb-2">System</label>
                        <select name="filter_system" id="filter_system" class="w-full border rounded px-3 py-2">
                            <option value="">All Systems</option>
                            @foreach($systems as $system)
                                <option value="{{ $system->id }}" {{ request('filter_system') == $system->id ? 'selected' : '' }}>{{ $system->nama_sistem }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">Apply Filter</button>
                        <a href="{{ route('parts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Active Filters -->
            @if(request()->hasAny(['filter_brand', 'filter_system', 'search']))
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(request('search'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                            Search: {{ request('search') }}
                            <a href="{{ route('parts.index', request()->except('search')) }}" class="ml-2 text-blue-600 hover:text-blue-800">×</a>
                        </span>
                    @endif
                    @if(request('filter_brand'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800">
                            Brand: {{ request('filter_brand') }}
                            <a href="{{ route('parts.index', request()->except('filter_brand')) }}" class="ml-2 text-purple-600 hover:text-purple-800">×</a>
                        </span>
                    @endif
                    @if(request('filter_system'))
                        @php
                            $selectedSystem = $systems->find(request('filter_system'));
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                            System: {{ $selectedSystem ? $selectedSystem->name : 'N/A' }}
                            <a href="{{ route('parts.index', request()->except('filter_system')) }}" class="ml-2 text-yellow-600 hover:text-yellow-800">×</a>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Part Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Brand</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Systems</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($parts as $part)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($parts->currentPage() - 1) * $parts->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $part->part_number ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $part->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $part->brand ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full {{ $part->stock > 10 ? 'bg-green-100 text-green-800' : ($part->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $part->stock }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $part->unit ?? 'pcs' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($part->price)
                                Rp {{ number_format($part->price, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($part->systems->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($part->systems as $system)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $system->nama_sistem }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('parts.edit', $part->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('parts.destroy', $part->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this sparepart?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">No spareparts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($parts->hasPages())
                <div class="mt-4">
                    {{ $parts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const search = this.value;
                    const url = new URL(window.location.href);
                    if (search) {
                        url.searchParams.set('search', search);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.location.href = url.toString();
                }
            });
        }
    });
</script>
@endpush
@endsection
