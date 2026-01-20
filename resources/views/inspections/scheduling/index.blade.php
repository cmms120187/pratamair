@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Inspeksi</h1>
            <a href="{{ route('inspections.scheduling.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Buat Jadwal
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('inspections.scheduling.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                    <select name="machine_type_id" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Machine Type</option>
                        @foreach($machineTypes as $type)
                            <option value="{{ $type->id }}" {{ request('machine_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau ID mesin..." class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('inspections.scheduling.index') }}" class="ml-2 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Judul</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Mesin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Frekuensi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Tanggal Mulai</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">PIC</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ $schedule->title }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $schedule->machineErp->idMachine ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $schedule->template->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ ucfirst($schedule->frequency) }} 
                                @if($schedule->frequency_value > 1)
                                    ({{ $schedule->frequency_value }}x)
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $schedule->start_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $schedule->assignedUser->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                @if($schedule->status == 'active')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Active</span>
                                @elseif($schedule->status == 'completed')
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">Completed</span>
                                @elseif($schedule->status == 'cancelled')
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-semibold">Cancelled</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('inspections.scheduling.show', $schedule->id) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('inspections.scheduling.edit', $schedule->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('inspections.scheduling.destroy', $schedule->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
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
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada jadwal inspeksi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

