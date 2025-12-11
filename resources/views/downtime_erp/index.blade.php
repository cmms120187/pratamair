@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Downtime</h1>
            <div class="flex items-center gap-3">
                <button type="button" @click="filterModalOpen = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['date_from', 'date_to', 'plant', 'process', 'line', 'room', 'typeMachine']))
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <form action="{{ route('downtime_erp.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,.tsv" required class="border rounded px-2 py-2 text-sm text-gray-700 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-3 rounded shadow transition flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Import CSV
                    </button>
                </form>
                <a href="{{ route('downtime_erp.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
        @error('csv_file')
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $message }}
            </div>
        @enderror
        
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed; width: 100%;">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Date</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Plant</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Process</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Line</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Room</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">ID Machine</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Machine</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Model</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Brand</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Stop</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Respon</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Start</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Duration</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Std Time</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 200px;">Problem</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Problem MM</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Reason</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 150px;">Action</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Part</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Mekanik</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Leader</th> -->
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Coord</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Group</th> -->
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $row)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-3 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                        <td class="px-3 py-3 text-sm text-gray-900" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->plant ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->process ?? '-' }}</td> -->
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->line ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->roomName ?? '-' }}</td> -->
                        <td class="px-3 py-3 text-sm text-gray-900 font-semibold" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->idMachine ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->typeMachine ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->modelMachine ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->brandMachine ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->stopProduction ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->responMechanic ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->startProduction ?? '-' }}</td> -->
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->duration ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->Standar_Time ?? '-' }}</td> -->
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->problemDowntime ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->Problem_MM ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->reasonDowntime ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->actionDowtime ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->Part ?? '-' }}</td> -->
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->nameMekanik ?? '-' }}</td>
                        <!-- <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->nameLeader ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->nameCoord ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word;">{{ $row->groupProblem ?? '-' }}</td> -->
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('downtime_erp.show', $row->id) }}" class="inline-flex items-center justify-center bg-yellow-600 hover:bg-yellow-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Show">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('downtime_erp.edit', $row->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('downtime_erp.destroy', $row->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="px-4 py-8 text-center text-sm text-gray-500">No downtime ERP data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($data->hasPages())
                <div class="mt-4">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Filter Modal -->
    <div x-show="filterModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="filterModalOpen = false"
         @keydown.escape.window="filterModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="filterModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6">
            <!-- Close Button -->
            <button @click="filterModalOpen = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Modal Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Settings
                </h2>
                <p class="text-sm text-gray-500 mt-1">Set your filter criteria and click Save to apply</p>
            </div>
            
            <!-- Modal Content -->
            <form method="GET" action="{{ route('downtime_erp.index') }}" id="filterForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                    <!-- Date From -->
                    <div>
                        <label for="modal_date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" id="modal_date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    
                    <!-- Date To -->
                    <div>
                        <label for="modal_date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" id="modal_date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    
                    <!-- Plant -->
                    <div>
                        <label for="modal_plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                        <select name="plant" id="modal_plant" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Plants</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant }}" {{ request('plant') == $plant ? 'selected' : '' }}>{{ $plant }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Process -->
                    <div>
                        <label for="modal_process" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                        <select name="process" id="modal_process" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Processes</option>
                            @foreach($processes as $process)
                                <option value="{{ $process }}" {{ request('process') == $process ? 'selected' : '' }}>{{ $process }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Line -->
                    <div>
                        <label for="modal_line" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                        <select name="line" id="modal_line" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Lines</option>
                            @foreach($lines as $line)
                                <option value="{{ $line }}" {{ request('line') == $line ? 'selected' : '' }}>{{ $line }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Room -->
                    <div>
                        <label for="modal_room" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                        <select name="room" id="modal_room" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room }}" {{ request('room') == $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Type Machine -->
                    <div>
                        <label for="modal_typeMachine" class="block text-sm font-medium text-gray-700 mb-2">Type Machine</label>
                        <select name="typeMachine" id="modal_typeMachine" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All Types</option>
                            @foreach($typeMachines as $typeMachine)
                                <option value="{{ $typeMachine }}" {{ request('typeMachine') == $typeMachine ? 'selected' : '' }}>{{ $typeMachine }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                    <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                        Cancel
                    </button>
                    <a href="{{ route('downtime_erp.index') }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
                        Reset Filter
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded text-sm transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection
