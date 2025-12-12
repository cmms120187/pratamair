@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Hasil Produksi Perhari</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('production-daily.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambah Data
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('production-daily.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                    <select name="line_id" id="line_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Semua Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ request('line_id') == $line->id ? 'selected' : '' }}>
                                {{ $line->name }} ({{ $line->process->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="process_id" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                    <select name="process_id" id="process_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Semua Process --</option>
                        @foreach($processes as $process)
                            <option value="{{ $process->id }}" {{ request('process_id') == $process->id ? 'selected' : '' }}>
                                {{ $process->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Line</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Process</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Jam Produksi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Target/Jam</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Target/Hari</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Total Produksi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade A</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade B</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade C</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($productionDaily as $item)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($productionDaily->currentPage() - 1) * $productionDaily->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->production_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $item->line->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $item->process->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">
                            @if($item->production_hours > 0)
                                {{ number_format($item->production_hours, 1, ',', '.') }} jam
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">
                            @if($item->target_per_hour > 0)
                                {{ number_format($item->target_per_hour, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">
                            @if($item->target_per_day > 0)
                                {{ number_format($item->target_per_day, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">{{ number_format($item->total_production, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right font-semibold">{{ number_format($item->grade_a, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 text-right">{{ number_format($item->grade_b, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 text-right">{{ number_format($item->grade_c, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('production-daily.edit', $item->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('production-daily.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data produksi ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($productionDaily->hasPages())
                <div class="mt-4">
                    {{ $productionDaily->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

