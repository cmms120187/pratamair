@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Work Orders</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('work-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Work Order
                </a>
            </div>
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('work-orders.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="waiting_parts" {{ request('status') == 'waiting_parts' ? 'selected' : '' }}>Menunggu Sparepart</option>
                        <option value="order_parts" {{ request('status') == 'order_parts' ? 'selected' : '' }}>Order Sparepart</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Diperbaiki</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <select name="priority" id="priority" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                    <select name="machine_id" id="machine_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">All Machines</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ request('machine_id') == $machine->id ? 'selected' : '' }}>
                                {{ $machine->idMachine }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                    <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="md:col-span-3 lg:col-span-6 flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('work-orders.index') }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">WO Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Machine</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Sparepart</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Assigned To</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Due Date</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workOrders as $wo)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $wo->order_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            <div class="font-medium">{{ $wo->machine->idMachine ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $wo->machine->machineType->name ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="max-w-xs truncate" title="{{ $wo->description }}">{{ $wo->description }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if($wo->parts && $wo->parts->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($wo->parts as $part)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800" title="{{ $part->name }}">
                                            {{ $part->name }}
                                            @if($part->pivot->quantity > 1)
                                                <span class="font-semibold">({{ $part->pivot->quantity }})</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $wo->getPriorityBadgeClass() }}">
                                {{ $wo->getPriorityLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $wo->getStatusBadgeClass() }}">
                                {{ $wo->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            {{ $wo->assignedTo->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            {{ $wo->due_date ? $wo->due_date->format('Y-m-d') : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('work-orders.show', $wo->id) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @if(Auth::user()->role !== 'mekanik')
                                    <a href="{{ route('work-orders.edit', $wo->id) }}{{ request()->hasAny(['status', 'priority', 'machine_id', 'assigned_to', 'date_from', 'date_to', 'page']) ? '?' . http_build_query(request()->only(['status', 'priority', 'machine_id', 'assigned_to', 'date_from', 'date_to', 'page'])) : '' }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('work-orders.destroy', $wo->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus work order ini?');">
                                    @csrf
                                    @method('DELETE')
                                    @if(request('status'))
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                    @endif
                                    @if(request('priority'))
                                        <input type="hidden" name="priority" value="{{ request('priority') }}">
                                    @endif
                                    @if(request('machine_id'))
                                        <input type="hidden" name="machine_id" value="{{ request('machine_id') }}">
                                    @endif
                                    @if(request('assigned_to'))
                                        <input type="hidden" name="assigned_to" value="{{ request('assigned_to') }}">
                                    @endif
                                    @if(request('date_from'))
                                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                    @endif
                                    @if(request('date_to'))
                                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                    @endif
                                    @if(request('page'))
                                        <input type="hidden" name="page" value="{{ request('page') }}">
                                    @endif
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            Tidak ada work order ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $workOrders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
