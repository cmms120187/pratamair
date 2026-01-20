@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Update Inspeksi</h1>
            <a href="{{ route('inspections.updating.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Buat Inspeksi
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('inspections.updating.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jadwal</label>
                    <select name="schedule_id" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Jadwal</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}" {{ request('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                {{ $schedule->title }} - {{ $schedule->machineErp->idMachine ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mesin</label>
                    <select name="machine_erp_id" class="w-full border rounded px-3 py-2">
                        <option value="">Semua Mesin</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ request('machine_erp_id') == $machine->id ? 'selected' : '' }}>
                                {{ $machine->idMachine }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('inspections.updating.index') }}" class="ml-2 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Mesin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Dilakukan Oleh</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inspections as $inspection)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $loop->iteration + ($inspections->currentPage() - 1) * $inspections->perPage() }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $inspection->inspection_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $inspection->machine->idMachine ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $inspection->template->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $inspection->performedBy->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('inspections.show', $inspection->id) }}" class="text-blue-600 hover:text-blue-800" title="Lihat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('inspections.updating.edit', $inspection->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data inspeksi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $inspections->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

