@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Machine</h1>
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('filterModal').classList.remove('hidden')" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['filter_id_machine', 'filter_kode_room', 'filter_plant_name', 'filter_process_name', 'filter_line_name', 'filter_room_name', 'filter_type_name', 'filter_brand_name', 'filter_model_name']))
                        <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <form action="{{ route('machine-erp.synchronize') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Synchronize Type, Model, dan Brand dari tabel Models? Ini akan mengupdate kolom Type, Model, dan Brand di Machine ERP sesuai data di tabel Models.')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        SYNCHRON
                    </button>
                </form>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('machine-erp.download') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
                <a href="{{ route('machine-erp.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Active Filters -->
        @if(request()->hasAny(['filter_id_machine', 'filter_kode_room', 'filter_plant_name', 'filter_process_name', 'filter_line_name', 'filter_room_name', 'filter_type_name', 'filter_brand_name', 'filter_model_name']))
            <div class="mb-4 flex flex-wrap gap-2">
                @if(request('filter_id_machine'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                        ID Machine: {{ request('filter_id_machine') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_id_machine')) }}" class="ml-2 text-blue-600 hover:text-blue-800">×</a>
                    </span>
                @endif
                @if(request('filter_kode_room'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800">
                        Kode Room: {{ request('filter_kode_room') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_kode_room')) }}" class="ml-2 text-purple-600 hover:text-purple-800">×</a>
                    </span>
                @endif
                @if(request('filter_plant_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                        Plant: {{ request('filter_plant_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_plant_name')) }}" class="ml-2 text-green-600 hover:text-green-800">×</a>
                    </span>
                @endif
                @if(request('filter_process_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                        Process: {{ request('filter_process_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_process_name')) }}" class="ml-2 text-yellow-600 hover:text-yellow-800">×</a>
                    </span>
                @endif
                @if(request('filter_line_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800">
                        Line: {{ request('filter_line_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_line_name')) }}" class="ml-2 text-indigo-600 hover:text-indigo-800">×</a>
                    </span>
                @endif
                @if(request('filter_room_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-pink-100 text-pink-800">
                        Room: {{ request('filter_room_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_room_name')) }}" class="ml-2 text-pink-600 hover:text-pink-800">×</a>
                    </span>
                @endif
                @if(request('filter_type_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">
                        Type: {{ request('filter_type_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_type_name')) }}" class="ml-2 text-red-600 hover:text-red-800">×</a>
                    </span>
                @endif
                @if(request('filter_brand_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-teal-100 text-teal-800">
                        Brand: {{ request('filter_brand_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_brand_name')) }}" class="ml-2 text-teal-600 hover:text-teal-800">×</a>
                    </span>
                @endif
                @if(request('filter_model_name'))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-orange-100 text-orange-800">
                        Model: {{ request('filter_model_name') }}
                        <a href="{{ route('machine-erp.index', request()->except('filter_model_name')) }}" class="ml-2 text-orange-600 hover:text-orange-800">×</a>
                    </span>
                @endif
                <a href="{{ route('machine-erp.index') }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800 hover:bg-gray-200">
                    Clear All
                </a>
            </div>
        @endif
        
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID Machine</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Kode Room</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plant Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Process Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Line Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Room Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Brand Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Model Name</th>
                        <!-- <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Serial Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tahun Production</th> -->
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($machineErps as $machineErp)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($machineErps->currentPage() - 1) * $machineErps->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $machineErp->idMachine }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->kode_room ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->plant_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->process_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->line_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->room_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->type_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->brand_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->model_name ?? '-' }}</td>
                        <!-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->serial_number ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineErp->tahun_production ?? '-' }}</td> -->
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('machine-erp.show', $machineErp->id) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                @if(Auth::user()->role !== 'mekanik')
                                    <a href="{{ route('machine-erp.edit', ['machine_erp' => $machineErp->id, 'page' => $machineErps->currentPage()]) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                @endif
                                <form action="{{ route('machine-erp.destroy', $machineErp->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this machine ERP?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-8 text-center text-sm text-gray-500">No machine ERP found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($machineErps->hasPages())
                <div class="mt-4">
                    {{ $machineErps->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload Modal - Admin Only -->
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
            <form action="{{ route('machine-erp.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Excel File (.xlsx, .xls)</label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-2 text-xs text-gray-500">Format Excel: Kolom pertama harus header (idMachine, kode_room [opsional], plant_name, process_name, line_name, room_name, type_name, brand_name, model_name, serial_number, tahun_production, no_document, photo). Jika kode_room diisi, akan auto-fill plant_name, process_name, line_name, dan room_name dari RoomERP.</p>
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

<!-- Filter Modal -->
<div id="filterModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('filterModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Custom Filter Machine ERP</h3>
                <button onclick="document.getElementById('filterModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('machine-erp.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="filter_id_machine" class="block text-sm font-semibold text-gray-700 mb-2">ID Machine</label>
                        <input type="text" 
                               name="filter_id_machine" 
                               id="filter_id_machine" 
                               value="{{ request('filter_id_machine') }}" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter ID Machine">
                    </div>
                    
                    <div>
                        <label for="filter_kode_room" class="block text-sm font-semibold text-gray-700 mb-2">Kode Room</label>
                        <input type="text" 
                               name="filter_kode_room" 
                               id="filter_kode_room" 
                               value="{{ request('filter_kode_room') }}" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter Kode Room">
                    </div>
                    
                    <div>
                        <label for="filter_plant_name" class="block text-sm font-semibold text-gray-700 mb-2">Plant Name</label>
                        <select name="filter_plant_name" id="filter_plant_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Plants</option>
                            @foreach($plantNames ?? [] as $plant)
                                <option value="{{ $plant }}" {{ request('filter_plant_name') == $plant ? 'selected' : '' }}>{{ $plant }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_process_name" class="block text-sm font-semibold text-gray-700 mb-2">Process Name</label>
                        <select name="filter_process_name" id="filter_process_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Processes</option>
                            @foreach($processNames ?? [] as $process)
                                <option value="{{ $process }}" {{ request('filter_process_name') == $process ? 'selected' : '' }}>{{ $process }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_line_name" class="block text-sm font-semibold text-gray-700 mb-2">Line Name</label>
                        <select name="filter_line_name" id="filter_line_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Lines</option>
                            @foreach($lineNames ?? [] as $line)
                                <option value="{{ $line }}" {{ request('filter_line_name') == $line ? 'selected' : '' }}>{{ $line }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_room_name" class="block text-sm font-semibold text-gray-700 mb-2">Room Name</label>
                        <select name="filter_room_name" id="filter_room_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Rooms</option>
                            @foreach($roomNames ?? [] as $room)
                                <option value="{{ $room }}" {{ request('filter_room_name') == $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_type_name" class="block text-sm font-semibold text-gray-700 mb-2">Type Name</label>
                        <select name="filter_type_name" id="filter_type_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            @foreach($typeNames ?? [] as $type)
                                <option value="{{ $type }}" {{ request('filter_type_name') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_brand_name" class="block text-sm font-semibold text-gray-700 mb-2">Brand Name</label>
                        <select name="filter_brand_name" id="filter_brand_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Brands</option>
                            @foreach($brandNames ?? [] as $brand)
                                <option value="{{ $brand }}" {{ request('filter_brand_name') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter_model_name" class="block text-sm font-semibold text-gray-700 mb-2">Model Name</label>
                        <select name="filter_model_name" id="filter_model_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Models</option>
                            @foreach($modelNames ?? [] as $model)
                                <option value="{{ $model }}" {{ request('filter_model_name') == $model ? 'selected' : '' }}>{{ $model }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Apply Filter</button>
                    <a href="{{ route('machine-erp.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition">Reset</a>
                    <button type="button" onclick="document.getElementById('filterModal').classList.add('hidden')" class="text-gray-600 hover:text-gray-800">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

